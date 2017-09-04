<?php

namespace AppBundle\Entity\Repository;


use AppBundle\Entity\Service;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function getUserByRestoreToken($token)
    {
        $qb = $this->createQueryBuilder('u');
        $qb->select(
            'u'
        )
            ->where(
                $qb->expr()->eq('u.restoreToken', ':token'),
                $qb->expr()->gt('u.restoreExpire', ':now')
            )
            ->setParameters([
                'token' => $token,
                'now' => new \DateTime()
            ]);
        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }

    public function getReferUser($code)
    {
        /** @var User[] $userCollection */
        $userCollection = $this->findAll();
        foreach ($userCollection as $user){
            $hash = $this->getHash($user);
            if($hash == $code){
                return $user;
            }
        }

        return null;
    }

    public function getHash(User $user)
    {
        return hash('crc32', $user->getId(). $user->getEmail());
    }
}