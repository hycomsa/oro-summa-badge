<?php

namespace Summa\Bundle\BadgeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\CronBundle\Entity\ScheduleIntervalsAwareInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Summa\Bundle\BadgeBundle\Model\ExtendBadge;

/**
 * @ORM\Entity(repositoryClass="Summa\Bundle\BadgeBundle\Entity\Repository\BadgeRepository")
 * @ORM\Table(name="summa_badge")
 * @ORM\HasLifecycleCallbacks
 * @Config(
 *      routeName="summa_badge_index",
 *      routeView="summa_badge_view",
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-certificate"
 *          },
 *          "grid"={
 *              "default"="summa-badge-grid"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="commerce",
 *          }
 *      }
 * )
 */
class Badge extends ExtendBadge implements ScheduleIntervalsAwareInterface
{
    const SUMMA_BADGE_POSITION = 'summa_badge_position';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=150, nullable=false)
     *
     */
    protected $name;

    /**
     * @var File
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\AttachmentBundle\Entity\File")
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $image;

    /**
     * @ORM\Column(name="style", type="string", nullable=true)
     */
    protected $style;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $active;

    /**
     * @var string
     * @ORM\Column(name="product_assignment_rule", type="text", nullable=true)
     */
    protected $productAssignmentRule;

    /**
     * @var integer
     * @ORM\Column(name="apply_for_n_days", type="integer", nullable=true)
     */
    protected $applyForNDays;

    /**
     * @var BadgeSchedule[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="Summa\Bundle\BadgeBundle\Entity\BadgeSchedule",
     *      mappedBy="badge",
     *      cascade={"persist"},
     *      orphanRemoval=true
     * )
     * @ORM\OrderBy({"activeAt" = "ASC"})
     */
    protected $schedules;

    /**
     * @var bool
     * @ORM\Column(name="contain_schedule", type="boolean")
     */
    protected $containSchedule = false;

//    /**
//     * @var ArrayCollection|Product[]
//     *
//     * @ORM\OneToMany(
//     *      targetEntity="Summa\Bundle\BadgeBundle\Entity\BadgeToProduct",
//     *      mappedBy="product",
//     *      cascade={"all"},
//     *      orphanRemoval=true
//     * )
//     */
//    protected $products;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="summa.productbadge.badge.created_at"
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="summa.productbadge.badge.updated_at"
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $updatedAt;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->schedules    = new ArrayCollection();
//        $this->products     = new ArrayCollection();

        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return File
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param File $image
     * @return $this
     */
    public function setImage(File $image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime|null $createdAt
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime|null $updatedAt
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * @param mixed $style
     */
    public function setStyle($style)
    {
        $this->style = $style;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param $active
     * @return $this
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return ArrayCollection|BadgeSchedule[]
     */
    public function getSchedules()
    {
        return $this->schedules;
    }

    /**
     * @param ArrayCollection|BadgeSchedule[] $schedules
     * @return $this
     */
    public function setSchedules($schedules)
    {
        $this->schedules = $schedules;

        return $this;
    }

    /**
     * @param BadgeSchedule $schedule
     * @return $this
     */
    public function addSchedule(BadgeSchedule $schedule)
    {
        $schedule->setBadge($this);
        $this->schedules->add($schedule);
        $this->containSchedule = true;

        return $this;
    }

    /**
     * @param BadgeSchedule $schedule
     * @return $this
     */
    public function removeSchedule(BadgeSchedule $schedule)
    {
        $this->schedules->removeElement($schedule);
        $this->refreshContainSchedule();

        return $this;
    }

    /**
     * @return boolean
     */
    public function isContainSchedule()
    {
        return $this->containSchedule;
    }

    /**
     * @param boolean $containSchedule
     * @return Badge
     */
    public function setContainSchedule($containSchedule)
    {
        $this->containSchedule = $containSchedule;

        return $this;
    }

    public function refreshContainSchedule()
    {
        $this->setContainSchedule(!$this->schedules->isEmpty());
    }

    /**
     * @return string
     */
    public function getProductAssignmentRule()
    {
        return $this->productAssignmentRule;
    }

    /**
     * @param string|null $productAssignmentRule
     * @return $this
     */
    public function setProductAssignmentRule(?string $productAssignmentRule)
    {
        $this->productAssignmentRule = $productAssignmentRule;

        return $this;
    }

//    /**
//     * @return ArrayCollection|Product[]
//     */
//    public function getProducts()
//    {
//        return $this->products;
//    }

    /**
     * @return int
     */
    public function getApplyForNDays()
    {
        return $this->applyForNDays;
    }

    /**
     * @param int|null $applyForNDays
     * @return $this
     */
    public function setApplyForNDays(?int $applyForNDays)
    {
        $this->applyForNDays = $applyForNDays;
        return $this;
    }


    /**
     * Pre persist event handler.
     *
     * @ORM\PrePersist
     */
    public function beforeSave()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Pre update event handler.
     *
     * @ORM\PreUpdate
     */
    public function doUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }
}