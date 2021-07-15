<?php
 namespace App\Entity;
 use Doctrine\ORM\Mapping as ORM;
 use Symfony\Component\Validator\Constraints as Assert;
 use ApiPlatform\Core\Annotation\ApiResource;
 use Doctrine\Common\Collections\ArrayCollection;
 use Doctrine\Common\Collections\Collection;

 /**
  * @ORM\Entity(repositoryClass="App\Repository\CourseRepository")
  * @ORM\Table(name="course")
  * @ORM\HasLifecycleCallbacks()
  */
 class Course implements \JsonSerializable {
  /**
   * @ORM\Column(type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private ?int $id;

  /**
   * @ORM\Column(type="string", length=100)
   *
   */
  private $name;

  /**
   * @ORM\Column(type="time")
   */
  private $start_hour;

  /**
   * @ORM\Column(type="time")
   */
  private $end_hour;

  /**
   * @ORM\Column(type="datetime")
   */
  private $create_date;

    /**
     * @ORM\ManyToOne(targetEntity="Instructor", inversedBy="courses")
     */
    private $instructor;
    
    /**
    * @ORM\ManyToMany(targetEntity="Student", mappedBy="courses")
    */
   private $students;

    public function getInstructor(): ?Instructor
    {
        return $this->instructor;
    }

    public function setInstructor(?Instructor $instructor): self
    {
        $this->instructor = $instructor;
        return $this;
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
   * @return \DateTime
   */
  public function getStartHour()
  {
   return $this->start_hour->format('H:i:s');
  }
  /**
   * @param \DateTime $start_hour
   */
  public function setStartHour(\DateTime $start_hour)
  {
   $this->start_hour = $start_hour;
  }

  /**
   * @return \DateTime
   */
  public function getEndHour()
  {
   return $this->end_hour->format('H:i:s');;
  }

  /**
   * @param \DateTime $end_hour
   */
  public function setEndHour(\DateTime $end_hour)
  {
   $this->end_hour = $end_hour;
  }

  public function addStudent(Student $student): self
    {
        $this->students[] = $student;
 
        return $this;
    }
 
    public function removeStudent(Student $student): bool
    {
        return $this->students->removeElement($student);
    }
 
    public function getStudents(): Collection
    {
        return $this->students;
    }
  /**
   * @throws \Exception
   * @ORM\PrePersist()
   */
  public function beforeSave(){

   $this->create_date = new \DateTime('now', new \DateTimeZone('America/New_York'));
  }

  /**
   * Init the many to many
   */
  public function __construct()
   {
       $this->students = new ArrayCollection();
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
    "start_hour" => $this->getStartHour(),
    "end_hour" => $this->getEndHour(),
    "instructor" => $this->getInstructor()->getName(),
   ];
  }
 }