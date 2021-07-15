<?php
 namespace App\Controller;


 use App\Entity\Instructor;
 use App\Repository\InstructorRepository;
 use Doctrine\ORM\EntityManagerInterface;
 use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
 use Symfony\Component\HttpFoundation\JsonResponse;
 use Symfony\Component\HttpFoundation\Request;
 use Symfony\Component\Routing\Annotation\Route;

 /**
  * Class InstructorController
  * @package App\Controller
  * @Route("/api", name="instructor_api")
  */
 class InstructorController extends AbstractController
 {
  /**
   * @param InstructorRepository $instructorRepository
   * @return JsonResponse
   * @Route("/instructors", name="instructors", methods={"GET"})
   */
  public function getInstructors(InstructorRepository $instructorRepository){
   $data = $instructorRepository->findAll();
   return $this->response($data);
  }

  /**
   * @param Request $request
   * @param EntityManagerInterface $entityManager
   * @param InstructorRepository $instructorRepository
   * @return JsonResponse
   * @throws \Exception
   * @Route("/instructors", name="instructors_add", methods={"POST"})
   */
  public function addInstructor(Request $request, EntityManagerInterface $entityManager, InstructorRepository $instructorRepository){

   try{
    $request = $this->transformJsonBody($request);

    if (!$request || !$request->get('name')){
     throw new \Exception();
    }

    $instructor = new Instructor();
    $instructor->setName($request->get('name'));
    $entityManager->persist($instructor);
    $entityManager->flush();

    $data = [
     'status' => 200,
     'success' => "Instructor added successfully",
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
   * @param InstructorRepository $instructorRepository
   * @param $id
   * @return JsonResponse
   * @Route("/instructors/{id}", name="instructors_get", methods={"GET"})
   */
  public function getInstructor(InstructorRepository $instructorRepository, $id){
   $instructor = $instructorRepository->find($id);

   if (!$instructor){
    $data = [
     'status' => 404,
     'errors' => "Instructor not found",
    ];
    return $this->response($data, 404);
   }
   return $this->response($instructor);
  }


  /**
   * @param InstructorRepository $instructorRepository
   * @param $name
   * @return JsonResponse
   * @Route("/instructors/name/{name}", name="instructors_get_by_name", methods={"GET"})
   */
  public function getInstructorByName(InstructorRepository $instructorRepository, $name){
   $instructors = $instructorRepository->findByName($name);
   return $this->response($instructors);
  }

  /**
   * @param Request $request
   * @param EntityManagerInterface $entityManager
   * @param InstructorRepository $instructorRepository
   * @param $id
   * @return JsonResponse
   * @Route("/instructors/{id}", name="instructors_put", methods={"PUT"})
   */
  public function updateInstructor(Request $request, EntityManagerInterface $entityManager, InstructorRepository $instructorRepository, $id){

   try{
    $instructor = $instructorRepository->find($id);

    if (!$instructor){
     $data = [
      'status' => 404,
      'errors' => "Instructor not found",
     ];
     return $this->response($data, 404);
    }

    $request = $this->transformJsonBody($request);

    if (!$request || !$request->get('name')){
     throw new \Exception();
    }

    $instructor->setName($request->get('name'));
    $entityManager->flush();

    $data = [
     'status' => 200,
     'errors' => "Instructor updated successfully",
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
   * @param InstructorRepository $instructorRepository
   * @param $id
   * @return JsonResponse
   * @Route("/instructors/{id}", name="instructors_delete", methods={"DELETE"})
   */
  public function deleteInstructor(EntityManagerInterface $entityManager, InstructorRepository $instructorRepository, $id){
   $instructor = $instructorRepository->find($id);

   if (!$instructor){
    $data = [
     'status' => 404,
     'errors' => "Instructor not found",
    ];
    return $this->response($data, 404);
   }

   $entityManager->remove($instructor);
   $entityManager->flush();
   $data = [
    'status' => 200,
    'errors' => "Instructor deleted successfully",
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