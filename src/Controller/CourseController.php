<?php
 namespace App\Controller;


 use App\Entity\Course;
 use App\Entity\Instructor;
 use App\Repository\CourseRepository;
 use Doctrine\ORM\EntityManagerInterface;
 use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
 use Symfony\Component\HttpFoundation\JsonResponse;
 use Symfony\Component\HttpFoundation\Request;
 use Symfony\Component\Routing\Annotation\Route;

 use Psr\Log\LoggerInterface;
 /**
  * Class CourseController
  * @package App\Controller
  * @Route("/api", name="course_api")
  */
 class CourseController extends AbstractController
 {
  /**
   * @param CourseRepository $courseRepository
   * @return JsonResponse
   * @Route("/courses", name="courses", methods={"GET"})
   */
  public function getCourses(CourseRepository $courseRepository){
   $data = $courseRepository->findAll();
   return $this->response($data);
  }

  /**
   * @param Request $request
   * @param EntityManagerInterface $entityManager
   * @param CourseRepository $courseRepository
   * @return JsonResponse
   * @throws \Exception
   * @Route("/courses", name="courses_add", methods={"POST"})
   */
  public function addCourse(LoggerInterface $logger, Request $request, EntityManagerInterface $entityManager, CourseRepository $courseRepository){

   //try{
    $request = $this->transformJsonBody($request);

    if (!$request || !$request->get('name') || !$request->get('instructor_id') || !$request->get('start_hour') || !$request->get('duration')){
     throw new \Exception("Missing or invalid data");
    }

    $data = $courseRepository->findAlreadyBooked($request->get('instructor_id'), $request->get('start_hour'), $request->get('duration'));
    if($data){
        throw new \Exception("Instructor is busy in this period");
    }
    $instructor = $entityManager->getRepository(Instructor::class)->findOneById($request->get('instructor_id'));

    $course = new Course();
    $course->setName($request->get('name'));
    $course->setInstructor($instructor);
    $start = new \DateTime($request->get('start_hour'));
    $course->setStartHour($start);
    $end = clone($start);
    $end->modify("+".$request->get('duration').' minutes');
    $course->setEndHour($end);
    $entityManager->persist($course);
    $entityManager->flush();

    $data = [
     'status' => 200,
     'success' => "Course added successfully",
    ];
    return $this->response($data);

   // }catch (\Exception $e){
   //  $data = [
   //   'status' => 422,
   //   'errors' => $e->getMessage(),
   //  ];
   //  return $this->response($data, 422);
   // }

  }


  /**
   * @param CourseRepository $courseRepository
   * @param $id
   * @return JsonResponse
   * @Route("/courses/{id}", name="courses_get", methods={"GET"})
   */
  public function getCourse(CourseRepository $courseRepository, $id){
   $course = $courseRepository->find($id);

   if (!$course){
    $data = [
     'status' => 404,
     'errors' => "Course not found",
    ];
    return $this->response($data, 404);
   }
   return $this->response($course);
  }

  /**
   * @param Request $request
   * @param EntityManagerInterface $entityManager
   * @param CourseRepository $courseRepository
   * @param $id
   * @return JsonResponse
   * @Route("/courses/{id}", name="courses_put", methods={"PUT"})
   */
  public function updateCourse(Request $request, EntityManagerInterface $entityManager, CourseRepository $courseRepository, $id){

   try{
    $course = $courseRepository->find($id);

    if (!$course){
     $data = [
      'status' => 404,
      'errors' => "Course not found",
     ];
     return $this->response($data, 404);
    }

    $request = $this->transformJsonBody($request);

    if (!$request || !$request->get('name')){
     throw new \Exception();
    }

    $course->setName($request->get('name'));
    $entityManager->flush();

    $data = [
     'status' => 200,
     'errors' => "Course updated successfully",
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
   * @param CourseRepository $courseRepository
   * @param $id
   * @return JsonResponse
   * @Route("/courses/{id}", name="courses_delete", methods={"DELETE"})
   */
  public function deleteCourse(EntityManagerInterface $entityManager, CourseRepository $courseRepository, $id){
   $course = $courseRepository->find($id);

   if (!$course){
    $data = [
     'status' => 404,
     'errors' => "Course not found",
    ];
    return $this->response($data, 404);
   }

   $entityManager->remove($course);
   $entityManager->flush();
   $data = [
    'status' => 200,
    'errors' => "Course deleted successfully",
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