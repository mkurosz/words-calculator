<?php

namespace App\Dto;

use JMS\Serializer\Annotation\Type;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class WordsInputDto.
 */
class TextInputDto
{
    /**
     * Text.
     *
     * @var string|null
     * @Type("string")
     *
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */
    private $text;

    /**
     * WordsInputDto constructor.
     *
     * @param string|null $text
     */
    public function __construct(?string $text)
    {
        $this->text = $text;
    }

    /**
     * Get text.
     *
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->text;
    }
}
