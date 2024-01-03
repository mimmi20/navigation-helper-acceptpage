<?php
/**
 * This file is part of the mimmi20/navigation-helper-acceptpage package.
 *
 * Copyright (c) 2021-2024, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\NavigationHelper\Accept;

use Laminas\Navigation\Page\AbstractPage;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Mimmi20\Mezzio\GenericAuthorization\AuthorizationInterface;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;

final class AcceptHelper implements AcceptHelperInterface
{
    /** @throws void */
    public function __construct(
        private readonly Acl | AuthorizationInterface | null $authorization,
        private readonly bool $renderInvisible,
        private readonly string | null $role,
    ) {
        // nothing to do
    }

    /**
     * Determines whether a page should be accepted when iterating
     * Rules:
     * - If a page is not visible it is not accepted, unless RenderInvisible has
     *   been set to true
     * - If $useAuthorization is true (default is true):
     *      - Page is accepted if Authorization returns true, otherwise false
     * - If page is accepted and $recursive is true, the page
     *   will not be accepted if it is the descendant of a non-accepted page
     *
     * @param AbstractPage|PageInterface $page      page to check
     * @param bool                       $recursive [optional] if true, page will not be accepted, if it is the descendant of a page that is not accepted.
     *                                              Default is true
     *
     * @return bool Whether page should be accepted
     *
     * @throws void
     */
    public function accept(AbstractPage | PageInterface $page, bool $recursive = true): bool
    {
        if (!$page->isVisible(false) && !$this->renderInvisible) {
            return false;
        }

        $accept    = true;
        $resource  = $page->getResource();
        $privilege = $page->getPrivilege();

        if ($resource instanceof ResourceInterface) {
            $resource = $resource->getResourceId();
        }

        if ($resource !== null || $privilege !== null) {
            if ($this->authorization instanceof AuthorizationInterface) {
                $accept = $this->authorization->isGranted($this->role, $resource, $privilege);
            }

            if ($this->authorization instanceof Acl) {
                $accept = $this->authorization->isAllowed($this->role, $resource, $privilege);
            }
        }

        if ($accept && $recursive) {
            $parent = $page->getParent();

            if ($parent instanceof PageInterface || $parent instanceof AbstractPage) {
                $accept = $this->accept($parent, true);
            }
        }

        return $accept;
    }

    /** @throws void */
    public function getAuthorization(): Acl | AuthorizationInterface | null
    {
        return $this->authorization;
    }

    /** @throws void */
    public function getRenderInvisible(): bool
    {
        return $this->renderInvisible;
    }

    /** @throws void */
    public function getRole(): string | null
    {
        return $this->role;
    }
}
