<?php
 namespace App\Controller;


 use App\Entity\Student;
 use App\Entity\Course;
 use App\Repository\StudentRepository;
 use Doctrine\ORM\EntityManagerInterface;
 use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
 use Symfony\Component\HttpFoundation\JsonResponse;
 use Symfony\Component\HttpFoundation\Request;
 use Symfony\Component\Routing\Annotation\Route;

 use Psr\Log\LoggerInterface;
 /**
  * Class StudentController
  * @package App\Controller
  * @Route("/api", name="student_api")
  */
 class StudentController extends AbstractController
 {
  /**
   * @param StudentRepository $studentRepository
   * @return JsonResponse
   * @Route("/students", name="students", methods={"GET"})
   */
  public function getStudents(StudentRepository $studentRepository){
   $data = $studentRepository->findAll();
   return $this->response($data);
  }

  /**
   * @param Request $request
   * @param EntityManagerInterface $entityManager
   * @param StudentRepository $studentRepository
   * @return JsonResponse
   * @throws \Exception
   * @Route("/students", name="students_add", methods={"POST"})
   */
  public function addStudent(Request $request, EntityManagerInterface $entityManager, StudentRepository $studentRepository){

   try{
    $request = $this->transformJsonBody($request);

    if (!$request || !$request->get('name')){
     throw new \Exception();
    }

    $student = new Student();
    $student->setName($request->get('name'));
    $entityManager->persist($student);
    $entityManager->flush();

    $data = [
     'status' => 200,
     'success' => "Student added successfully",
    ];
    return $this->response($data);

   }catch (\Exception $e){
    $data = [
     'status' => 422,
     'errors' => "Data no valid",
    ];
    return $this->response($data, 422);
   }

  }


  /**
   * @param StudentRepository $studentRepository
   * @param $id
   * @return JsonResponse
   * @Route("/students/{id}", name="students_get", methods={"GET"})
   */
  public function getStudent(StudentRepository $studentRepository, $id){
   $student = $studentRepository->find($id);

   if (!$student){
    $data = [
     'status' => 404,
     'errors' => "Student not found",
    ];
    return $this->response($data, 404);
   }
   return $this->response($student);
  }

  /**
   * @param Request $request
   * @param EntityManagerInterface $entityManager
   * @param StudentRepository $studentRepository
   * @return JsonResponse
   * @throws \Exception
   * @Route("/students/roll", name="students_course_toggle", methods={"POST"})
   */
  public function toggleCourse(LoggerInterface $logger, Request $request, EntityManagerInterface $entityManager, StudentRepository $studentRepository){
   try{
    $student = $studentRepository->find($request->get('id'));
    $course = $entityManager->getRepository(Course::class)->findOneById($request->get('course_id'));
    $dql = "SELECT c.id FROM App\Entity\Student s " .
        "JOIN s.courses c WHERE s.id=".$student->getId()." and c.id<>".$course->getId()." and 
        (('".$course->getStartHour()."' < c.end_hour and '".$course->getStartHour()."' >= c.start_hour) or ('".$course->getEndHour()."' <= c.end_hour and '".$course->getEndHour()."' > c.start_hour)) ORDER BY c.id DESC";
    $r = $entityManager->createQuery($dql)->getArrayResult();
    if(!empty($r)){
        throw new \Exception('Can\'t enroll mixed hours');
    }
    if (!$student || !$course){
     $data = [
      'status' => 404,
      'errors' => "Student or Course not found",
     ];
     return $this->response($data, 404);
    }

    $res = $student->removeCourse($course);
    if(!$res){
        $student->addCourse($course);
    }
    $entityManager->persist($student);
    $entityManager->flush();

    $data = [
     'status' => 200,
     'errors' => "Student updated successfully",
    ];
    return $this->response($data);

   }catch (\Exception $e){
    $data = [
     'status' => 422,
     'errors' => "Data no valid",
     'msg' => $e->getMessage(),
    ];
    return $this->response($data, 422);
   }

  }

  /**
   * @param Request $request
   * @param EntityManagerInterface $entityManager
   * @param StudentRepository $studentRepository
   * @param $id
   * @return JsonResponse
   * @Route("/students/{id}", name="students_put", methods={"PUT"})
   */
  public function updateStudent(Request $request, EntityManagerInterface $entityManager, StudentRepository $studentRepository, $id){

   try{
    $student = $studentRepository->find($id);

    if (!$student){
     $data = [
      'status' => 404,
      'errors' => "Student not found",
     ];
     return $this->response($data, 404);
    }

    $request = $this->transformJsonBody($request);

    if (!$request || !$request->get('name')){
     throw new \Exception();
    }

    $student->setName($request->get('name'));
    $entityManager->flush();

    $data = [
     'status' => 200,
     'errors' => "Student updated successfully",
    ];
    return $this->response($data);

   }catch (\Exception $e){
    $data = [
     'status' => 422,
     'errors' => "Data no valid",
    ];
    return $this->response($data, 422);
   }

  }


  /**
   * @param StudentRepository $studentRepository
   * @param $id
   * @return JsonResponse
   * @Route("/students/{id}", name="students_delete", methods={"DELETE"})
   */
  public function deleteStudent(EntityManagerInterface $entityManager, StudentRepository $studentRepository, $id){
   $student = $studentRepository->find($id);

   if (!$student){
    $data = [
     'status' => 404,
     'errors' => "Student not found",
    ];
    return $this->response($data, 404);
   }

   $entityManager->remove($student);
   $entityManager->flush();
   $data = [
    'status' => 200,
    'errors' => "Student deleted successfully",
   ];
   return $this->response($data);
  }





  /**
   * Returns a JSON response
   *
   * @param array $data
   * @param $status
   * @param array $headers
   * @return JsonResponse
   */
  public function response($data, $status = 200, $headers = [])
  {
   return new JsonResponse($data, $status, $headers);
  }

  protected function transformJsonBody(\Symfony\Component\HttpFoundation\Request $request)
  {
   $data = json_decode($request->getContent(), true);

   if ($data === null) {
    return $request;
   }

   $request->request->replace($data);

   return $request;
  }

 }