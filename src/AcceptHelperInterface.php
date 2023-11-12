<?php
/**
 * This file is part of the mimmi20/navigation-helper-acceptpage package.
 *
 * Copyright (c) 2021-2023, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\NavigationHelper\Accept;

use Laminas\Navigation\Page\AbstractPage;
use Mezzio\Navigation\Page\PageInterface;

interface AcceptHelperInterface
{
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
    public function accept(AbstractPage | PageInterface $page, bool $recursive = true): bool;
}
