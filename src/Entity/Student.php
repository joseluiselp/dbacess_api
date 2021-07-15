<?php
 namespace App\Entity;
 use Doctrine\ORM\Mapping as ORM;
 use Symfony\Component\Validator\Constraints as Assert;
 use Doctrine\Common\Collections\Collection;
 /**
  * @ORM\Entity
  * @ORM\Table(name="student")
  * @ORM\HasLifecycleCallbacks()
  */
 class Student implements \JsonSerializable {
  /**
   * @ORM\Column(type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @ORM\Column(type="string", length=200)
   *
   */
  private $name;

  /**
   * @ORM\Column(type="datetime")
   */
  private $create_date;

  /**
     * @ORM\ManyToMany(targetEntity="Course", inversedBy="students")
     */
    private $courses;

public function addCourse(Course $course): self
    {
        $this->courses[] = $course;
 
        return $this;
    }
 
    public function removeCourse(Course $course): bool
    {
        return $this->courses->removeElement($course);
    }

    /**
     * @return Collection|Course[]
     */
    public function getCourses(): Collection
    {
        return $this->courses;
    }
    
  /**
   * @return mixed
   */
  public function getId()
  {
   return $this->id;
  }
  /**
   * @param mixed $id
   */
  public function setId($id)
  {
   $this->id = $id;
  }
  /**
   * @return mixed
   */
  public function getName()
  {
   return $this->name;
  }
  /**
   * @param mixed $name
   */
  public function setName($name)
  {
   $this->name = $name;
  }
  /**
   * @return mixed[]
   */
  public function getCoursesIds()
  {
   $ids = array();
   foreach($this->getCourses() as $course){
    $ids[] = $course->getId();
   }
   return $ids;
  }
  /**
   * @throws \Exception
   * @ORM\PrePersist()
   */
  public function beforeSave(){

   $this->create_date = new \DateTime('now', new \DateTimeZone('America/New_York'));
  }



  /**
   * Specify data which should be serialized to JSON
   * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
   * @return mixed data which can be serialized by <b>json_encode</b>,
   * which is a value of any type other than a resource.
   * @since 5.4.0
   */
  public function jsonSerialize()
  {
   return [
    "id" => $this->getId(),
    "name" => $this->getName(),
    "courses" => $this->getCoursesIds(),
   ];
  }
 }