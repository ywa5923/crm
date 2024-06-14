<?php

namespace App\Security\Voter;

use App\Entity\Calendar;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class CalendarVoter extends Voter
{
    public const EDIT = 'CAL_EDIT';
    public const VIEW = 'CAL_VIEW';


    public function __construct(private Security $security,)
    {
    }
    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof \App\Entity\Calendar;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($subject, $user);
                break;

            case self::VIEW:
                return $this->canView($subject, $user);
                break;
        }

        return false;
    }

    public function canView(Calendar $calendar, User $user)
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // if they can edit, they can view
        if ($this->canEdit($calendar, $user)) {
            return true;
        }
        return false;
    }
    public function canEdit(Calendar $calendar, User $user)
    {
        return $calendar->getUser() === $user;
    }
}
