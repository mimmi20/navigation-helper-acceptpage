<?php

/**
 * This file is part of the mimmi20/navigation-helper-acceptpage package.
 *
 * Copyright (c) 2021-2025, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\NavigationHelper\Accept;

use Laminas\Navigation\Page\AbstractPage;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;
use Mimmi20\Mezzio\GenericAuthorization\AuthorizationInterface;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;
use Override;

use function assert;
use function count;
use function is_string;

final readonly class AcceptHelper implements AcceptHelperInterface
{
    /**
     * @param array<RoleInterface|string> $roles
     *
     * @throws void
     */
    public function __construct(
        private Acl | AuthorizationInterface | null $authorization,
        private bool $renderInvisible,
        private array $roles,
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
    #[Override]
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
                $accept = $this->grandFromAuth($resource, $privilege);
            }

            if ($this->authorization instanceof Acl) {
                $accept = $this->grandFromAcl($resource, $privilege);
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

    /**
     * @throws void
     *
     * @api
     */
    public function getAuthorization(): Acl | AuthorizationInterface | null
    {
        return $this->authorization;
    }

    /**
     * @throws void
     *
     * @api
     */
    public function getRenderInvisible(): bool
    {
        return $this->renderInvisible;
    }

    /**
     * @return array<RoleInterface|string>
     *
     * @throws void
     *
     * @api
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /** @throws void */
    private function grandFromAuth(string | null $resource, string | null $privilege): bool
    {
        $roles = $this->roles;

        if (count($roles)) {
            foreach ($roles as $role) {
                assert($this->authorization instanceof AuthorizationInterface);
                assert(is_string($role));

                if ($this->authorization->isGranted($role, $resource, $privilege)) {
                    return true;
                }
            }
        } else {
            assert($this->authorization instanceof AuthorizationInterface);

            if ($this->authorization->isGranted(null, $resource, $privilege)) {
                return true;
            }
        }

        return false;
    }

    /** @throws void */
    private function grandFromAcl(string | null $resource, string | null $privilege): bool
    {
        $roles = $this->roles;

        if (count($roles)) {
            foreach ($roles as $role) {
                assert($this->authorization instanceof Acl);

                if ($this->authorization->isAllowed($role, $resource, $privilege)) {
                    return true;
                }
            }
        } else {
            assert($this->authorization instanceof Acl);

            if ($this->authorization->isAllowed(null, $resource, $privilege)) {
                return true;
            }
        }

        return false;
    }
}
