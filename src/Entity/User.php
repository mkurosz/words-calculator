<?php

namespace App\Entity;

use App\Dto\TextInputDto;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Type;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(
 *      name="user",
 *      uniqueConstraints={@ORM\UniqueConstraint(columns={"ip_address"})}
 * )
 * @UniqueEntity(
 *      fields={"ipAddress"},
 *      message="User for given ip address already exists in database."
 * )
 * @ExclusionPolicy("all")
 */
class User
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
     * Ip address.
     *
     * @var string
     * @Type("string")
     *
     * @Assert\NotBlank()
     * @Assert\Ip
     *
     * @ORM\Column(type="string", length=255)
     *
     * @Expose
     */
    private $ipAddress;

    /**
     * Words.
     *
     * @var Word[]
     * @Type("ArrayCollection<App\Entity\Word>")
     *
     * @ORM\OneToMany(targetEntity=Word::class, mappedBy="user", orphanRemoval=true, cascade={"persist"})
     * @ORM\OrderBy({"count" = "DESC", "word" = "ASC"})
     *
     * @Expose
     */
    private $words;

    /**
     * User constructor.
     *
     * @param string $ipAddress
     */
    public function __construct(
        string $ipAddress
    ) {
        $this->ipAddress = $ipAddress;
        $this->words = new ArrayCollection();
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
     * Get ip address.
     *
     * @return string
     */
    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    /**
     * @return Collection|Word[]
     */
    public function getWords(): Collection
    {
        return $this->words;
    }

    /**
     * Add word.
     *
     * @param Word $word
     *
     * @return User
     */
    public function addWord(Word $word): self
    {
        if ($this->findWord($word->getWord()) === null) {
            $this->words[] = $word;
            $word->setUser($this);
        }

        return $this;
    }

    /**
     * Add new words.
     *
     * @param Word[] $newWords
     *
     * @return User
     */
    public function addWords(array $newWords): self
    {
        foreach ($newWords as $newWord) {
            $word = $this->findWord($newWord->getWord());

            if ($word === null) {
                $this->words[] = $newWord;
                $newWord->setUser($this);
            } else {
                $word->increaseCount($newWord->getCount());
            }
        }

        return $this;
    }

    /**
     * @param string $word
     *
     * @return Word|null
     */
    private function findWord(string $word): ?Word
    {
        $filtered = $this->words->filter(function(Word $filtered) use ($word) {
            return $filtered->getWord() === $word;
        });

        return $filtered->isEmpty() ? null : $filtered->first();
    }
}
