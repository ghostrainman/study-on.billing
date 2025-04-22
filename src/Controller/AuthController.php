<?php
namespace App\Controller;

use App\Dto\UserDto;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AuthController extends AbstractController
{
    #[Route('/api/v1/auth', name: 'api_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        //без этого метода ошибки при запросах, но он не юзается по факту
        return new JsonResponse(['message' => 'Authentication route'], 200);
    }

    #[Route('/api/v1/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $dto = $serializer->deserialize($request->getContent(), UserDto::class, 'json');
        $errors = $validator->validate($dto);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        // Проверка существование такого же email
        if ($em->getRepository(User::class)->findOneBy(['email' => $dto->username])) {
            return new JsonResponse(['error' => 'Пользователь с таким email уже существует'], 400);
        }

        // Сохраняем юзера
        $user = User::fromDto($dto, $hasher);
        $em->persist($user);
        $em->flush();

        // Генерация токена
        $token = $jwtManager->create($user);

        return new JsonResponse([
            'token' => $token,
            'username' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
        ], 201);
    }

    #[Route('/api/v1/users/current', name: 'api_current_user', methods: ['GET'])]
    public function current(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Вы не авторизованы'], 401);
        }

        return new JsonResponse([
            'username' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
            'balance' => $user->getBalance(),
        ]);
    }
}
