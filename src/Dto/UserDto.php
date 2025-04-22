<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Type;

class UserDto
{
    #[Assert\NotBlank(message: "Email обязателен")]
    #[Assert\Email(message: "Неверный Email")]
    #[Type("string")]
    public string $username;

    #[Assert\NotBlank(message: "Пароль обязателен")]
    #[Assert\Length(min: 6, minMessage: "Пароль должен быть не менее {{ limit }} символов")]
    #[Type("string")]
    public string $password;
}
