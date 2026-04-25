<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private UploadFileService $uploadFileService;
    private PaginatorInterface $paginator;


    private EntityManagerInterface $em;

    public function __construct(
        UserRepository              $userRepository,
        UploadFileService           $uploadFileService,
        UserPasswordHasherInterface $passwordHasher,
        PaginatorInterface          $paginator,
        EntityManagerInterface $em,
    ) {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->uploadFileService = $uploadFileService;
        $this->paginator = $paginator;
        $this->em = $em;
    }

    public function getList($page, $limit, $filters): PaginationInterface
    {
        $query = $this->userRepository->findAllPaginated($filters);
        return $this->paginator->paginate(
            $query,
            $page,
            $limit
        );
    }

    public function add(User $user, FormInterface $form, $files, $password): void
    {
        if ($password) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);
        }

        if ($files->has('avatar')) {
            $fileAvatar = $form->get('avatar')->getData();
            $fileFieldAvatar = $this->uploadFileService->uploadFile($fileAvatar, 'user');
            $user->setAvatarFileUrl($fileFieldAvatar['fileUrl']);
        }

        $this->userRepository->save($user);
    }

    public function update(User $user, FormInterface $form, bool $hasAvatar): void
    {
        if ($hasAvatar) {
            $file = $form->get('avatar')->getData();
            $filePath = $user->getAvatarFileUrl();
            $this->uploadFileService->removeFile($filePath);
            if (gettype($file) == "object") {
                $fileField = $this->uploadFileService->uploadFile($file, 'user');
                $user->setAvatarFileUrl($fileField['fileUrl']);
            } else {
                $user->setAvatarFileUrl(null);
            }
        }

        $this->userRepository->update();
    }

    public function delete(User $user): void
    {
        $this->userRepository->remove($user);
        $this->userRepository->update();
    }

    public function changeUserPassword(User $user, $newPassword): bool
    {
        $hashedNewPassword = $this->passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedNewPassword);
        $this->userRepository->update();
        return true;
    }

    public function isMatchedPassword(string $newPassword, string $confirmPassword): bool
    {
        return $newPassword == $confirmPassword;
    }

    public function getAllUserHasRoles(array $roles): array
    {
        return $this->userRepository->findUserByRoles($roles);
    }

    public function getUserCount()
    {
        return $this->userRepository->getUserCount()['count'];
    }
}
