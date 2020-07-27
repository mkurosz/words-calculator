<?php

namespace App\Controller;

use App\Dto\TextInputDto;
use App\Entity\User;
use App\Entity\Word;
use App\Repository\UserRepository;
use App\Repository\WordRepository;
use App\Service\TextProcessor;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swagger\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

/**
 * BoardsController.
 */
class UsersController extends AbstractFOSRestController
{
    /**
     * User repository.
     *
     * @var UserRepository
     */
    private $userRepository;

    /**
     * Word repository.
     *
     * @var WordRepository
     */
    private $wordRepository;

    /**
     * Text processor.
     *
     * @var TextProcessor
     */
    private $textProcessor;

    /**
     * Entity manager.
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * Validator.
     *
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * UsersController constructor.
     *
     * @param UserRepository $userRepository
     * @param WordRepository $wordRepository
     * @param TextProcessor $textProcessor
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     */
    public function __construct(
        UserRepository $userRepository,
        WordRepository $wordRepository,
        TextProcessor $textProcessor,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ) {
        $this->userRepository = $userRepository;
        $this->wordRepository = $wordRepository;
        $this->textProcessor = $textProcessor;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    /**
     * Get user by ip address.
     *
     * @param Request $request
     *
     * @return View
     *
     * @ParamConverter("user")
     *
     * @OA\Response(
     *     response=200,
     *     description="Returns the user data for given id.",
     *     @Model(type=User::class)
     * )
     */
    public function getUserAction(Request $request): View
    {
        try {
            $user = $this->userRepository->findOneBy([
                'ipAddress' => $request->getClientIp(),
            ]);

            if (!$user instanceof User) {
                return $this->view(
                    null,
                    Response::HTTP_NOT_FOUND
                );
            }

            return $this->view(
                $user,
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->createExceptionResponse($e);
        }
    }

    /**
     * Create user.
     *
     * @param Request $request
     *
     * @return View
     *
     * @OA\Response(
     *     response=200,
     *     description="Returns newly created user.",
     *     @Model(type=User::class)
     * )
     */
    public function postUserAction(Request $request): View
    {
        try {
            $newUser = new User($request->getClientIp());

            $validationErrors = $this->validator->validate($newUser);

            if ($validationErrors->count()) {
                return $this->createValidationErrorsResponse($validationErrors);
            }

            $this->entityManager->persist($newUser);
            $this->entityManager->flush();

            return $this->view($newUser);
        } catch (Exception $e) {
            return $this->createExceptionResponse($e);
        }
    }

    /**
     * Post words from text to user account.
     *
     * @param Request $request
     * @param TextInputDto $textInput
     *
     * @return View
     *
     * @ParamConverter("textInput", converter="fos_rest.request_body")
     *
     * @OA\Parameter(
     *     name="textInput",
     *     in="body",
     *     description="Text to process.",
     *     @Model(type=TextInputDto::class)
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns user words.",
     *     @OA\Schema(
     *         type="array",
     *         @OA\Items(ref=@Model(type=Word::class))
     *     )
     * )
     */
    public function postUsersWordsAction(Request $request, TextInputDto $textInput): View
    {
        try {
            $user = $this->userRepository->findOneBy([
                'ipAddress' => $request->getClientIp(),
            ]);

            if (!$user instanceof User) {
                return $this->view(
                    null,
                    Response::HTTP_NOT_FOUND
                );
            }

            $validationErrors = $this->validator->validate($textInput);

            if ($validationErrors->count()) {
                return $this->createValidationErrorsResponse($validationErrors);
            }

            $this->entityManager->persist(
                $user->addWords(
                    $this->textProcessor->parse(
                        $textInput,
                        $user
                    )
                )
            );
            $this->entityManager->flush();

            return $this->view(
                $this->wordRepository->findBy(
                    ['user' => $user],
                    ['count' => 'DESC', 'word' => 'ASC']
                )
            );
        } catch (Exception $e) {
            return $this->createExceptionResponse($e);
        }
    }

    /**
     * Create validation errors response.
     *
     * @param ConstraintViolationListInterface $validationErrors
     *
     * @return View
     */
    private function createValidationErrorsResponse(ConstraintViolationListInterface $validationErrors): View
    {
        $errors = [];

        /** @var ConstraintViolationInterface $validationError */
        foreach ($validationErrors as $validationError) {
            $errors[] = [
                'property' => $validationError->getPropertyPath(),
                'message' => $validationError->getMessage(),
            ];
        }

        return $this->view(['errors' => $errors], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Create exception response.
     *
     * @param Throwable $exception
     *
     * @return View
     */
    private function createExceptionResponse(Throwable $exception): View
    {
        return $this->view(
            [
                'errors' => [
                    'property' => '',
                    'message' => $exception->getMessage(),
                ]
            ],
            Response::HTTP_BAD_REQUEST
        );
    }
}
