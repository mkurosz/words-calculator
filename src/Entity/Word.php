<?php

namespace App\Entity;

use App\Repository\WordRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Type;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=WordRepository::class)
 * @UniqueEntity(
 *      fields={"user", "word"},
 *      message="Word for given user already exists in database."
 * )
 * @ExclusionPolicy("all")
 */
class Word
{
    /**
     * Id.
     *
     * @var int
     * @Type("integer")
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Expose
     */
    private $id;

    /**
     * Word.
     *
     * @var string
     * @Type("string")
     *
     * @ORM\Column(type="text")
     *
     * @Expose
     */
    private $word;

    /**
     * Count.
     *
     * @var int
     * @Type("integer")
     *
     * @ORM\Column(type="integer")
     *
     * @Expose
     */
    private $count;

    /**
     * User.
     *
     * @var User
     * @Type("App\Entity\User")
     *
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="words")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * Word constructor.
     *
     * @param string $word
     * @param int $count
     * @param User $user
     */
    public function __construct(
        string $word,
        int $count,
        User $user
    ) {
        $this->word = $word;
        $this->count = $count;
        $this->user = $user;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get word.
     *
     * @return string
     */
    public function getWord(): string
    {
        return $this->word;
    }

    /**
     * Get count.
     *
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * Set count.
     *
     * @param int $count
     *
     * @return Word
     */
    public function setCount(int $count): self
    {
        $this->count = $count;

        return $this;
    }

    /**
     * Increase count by given number.
     *
     * @param int $increase
     *
     * @return Word
     */
    public function increaseCount(int $increase): self
    {
        $this->count += $increase;

        return $this;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Set user.
     *
     * @param User $user
     *
     * @return Word
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
