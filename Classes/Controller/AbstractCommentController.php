<?php

namespace FelixNagel\T3extblog\Controller;

/**
 * This file is part of the "t3extblog" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use FelixNagel\T3extblog\Exception\InvalidConfigurationException;
use FelixNagel\T3extblog\Service\AuthenticationService;
use FelixNagel\T3extblog\Utility\FrontendUtility;
use FelixNagel\T3extblog\Domain\Model\Comment;

/**
 * PostController.
 *
 * @SuppressWarnings("PHPMD.ExcessivePublicCount")
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 */
abstract class AbstractCommentController extends AbstractController
{
    protected function getNewComment(): Comment
    {
        /* @var $comment Comment */
        $comment = $this->objectManager->get(Comment::class);

        // Shall we check for a user session?
        if (!$this->settings['blogsystem']['comments']['prefillFields']['enable']) {
            return $comment;
        }

        // In this case, the page needs to be uncached
        $this->clearPageCache();

        if (FrontendUtility::isUserLoggedIn()) {
            $comment->setEmail(FrontendUtility::getTsFe()->fe_user->user['email']);
            $comment->setAuthor($this->getNewCommentAuthor());

            return $comment;
        }

        /* @var $authentication AuthenticationService */
        $authentication = $this->objectManager->get(AuthenticationService::class);
        if ($authentication->isValid()) {
            $comment->setEmail($authentication->getEmail());

            return $comment;
        }

        return $comment;
    }

    protected function getNewCommentAuthor(): string
    {
        $field = $this->settings['blogsystem']['comments']['prefillFields']['authorField'];
        $user = FrontendUtility::getTsFe()->fe_user->user;

        if ($field === 'fullName') {
            $fullName = [];

            foreach (['first_name', 'middle_name', 'last_name'] as $item) {
                if (!empty($user[$item])) {
                    $fullName[] = $user[$item];
                }
            }

            return implode(' ', $fullName);
        }

        if (!array_key_exists($field, $user)) {
            throw new InvalidConfigurationException('Field does not exist!', 1596646025);
        }

        return $user[$field];
    }
}
