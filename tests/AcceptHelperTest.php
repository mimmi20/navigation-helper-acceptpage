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

namespace Mimmi20Test\NavigationHelper\Accept;

use Laminas\Navigation\AbstractContainer;
use Laminas\Navigation\Page\AbstractPage;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Mimmi20\Mezzio\Navigation\ContainerInterface;
use Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;
use Mimmi20\Mezzio\Navigation\Page\Uri;
use Mimmi20\Mezzio\GenericAuthorization\AuthorizationInterface;
use Mimmi20\NavigationHelper\Accept\AcceptHelper;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;

final class AcceptHelperTest extends TestCase
{
    /** @throws Exception */
    public function testDoNotAcceptInvisiblePages(): void
    {
        $role = 'testRole';
        $auth = $this->createMock(AuthorizationInterface::class);

        $helper = new AcceptHelper($auth, false, $role);

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(false);
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('hashCode');
        $page->expects(self::never())
            ->method('getOrder');
        $page->expects(self::never())
            ->method('setParent');

        self::assertFalse($helper->accept($page));
    }

    /** @throws Exception */
    public function testDoNotAcceptByAuthorization(): void
    {
        $role      = 'testRole';
        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::once())
            ->method('isGranted')
            ->with($role, $resource, $privilege)
            ->willReturn(false);

        $helper = new AcceptHelper($auth, false, $role);

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);
        $page->expects(self::once())
            ->method('getPrivilege')
            ->willReturn($privilege);
        $page->expects(self::never())
            ->method('hashCode');
        $page->expects(self::never())
            ->method('getOrder');
        $page->expects(self::never())
            ->method('setParent');

        self::assertFalse($helper->accept($page));
    }

    /** @throws Exception */
    public function testDoNotAcceptByAuthorizationWithParent(): void
    {
        $role      = 'testRole';
        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::once())
            ->method('isGranted')
            ->with($role, $resource, $privilege)
            ->willReturn(true);

        $helper = new AcceptHelper($auth, false, $role);

        $parentPage = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentPage->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(false);
        $parentPage->expects(self::never())
            ->method('hashCode');
        $parentPage->expects(self::never())
            ->method('getOrder');
        $parentPage->expects(self::never())
            ->method('setParent');

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);
        $page->expects(self::once())
            ->method('getPrivilege')
            ->willReturn($privilege);
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::never())
            ->method('hashCode');
        $page->expects(self::never())
            ->method('getOrder');
        $page->expects(self::never())
            ->method('setParent');

        self::assertFalse($helper->accept($page));
    }

    /** @throws Exception */
    public function testDoNotAcceptByAuthorizationWithParent2(): void
    {
        $role = 'testRole';

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $helper = new AcceptHelper($auth, false, $role);

        $parentPage = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentPage->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(false);
        $parentPage->expects(self::never())
            ->method('hashCode');
        $parentPage->expects(self::never())
            ->method('getOrder');
        $parentPage->expects(self::never())
            ->method('setParent');

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getResource')
            ->willReturn(null);
        $page->expects(self::once())
            ->method('getPrivilege')
            ->willReturn(null);
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::never())
            ->method('hashCode');
        $page->expects(self::never())
            ->method('getOrder');
        $page->expects(self::never())
            ->method('setParent');

        self::assertFalse($helper->accept($page));
    }

    /** @throws Exception */
    public function testDoNotAcceptByAuthorizationWithParent3(): void
    {
        $role      = 'testRole';
        $privilege = 'testPrivilege';

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::once())
            ->method('isGranted')
            ->with($role, null, $privilege)
            ->willReturn(true);

        $helper = new AcceptHelper($auth, false, $role);

        $parentPage = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentPage->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(false);
        $parentPage->expects(self::never())
            ->method('hashCode');
        $parentPage->expects(self::never())
            ->method('getOrder');
        $parentPage->expects(self::never())
            ->method('setParent');

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getResource')
            ->willReturn(null);
        $page->expects(self::once())
            ->method('getPrivilege')
            ->willReturn($privilege);
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::never())
            ->method('hashCode');
        $page->expects(self::never())
            ->method('getOrder');
        $page->expects(self::never())
            ->method('setParent');

        self::assertFalse($helper->accept($page));
    }

    /** @throws Exception */
    public function testDoNotAcceptByAuthorizationWithParent4(): void
    {
        $role     = 'testRole';
        $resource = 'testResource';

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::once())
            ->method('isGranted')
            ->with($role, $resource, null)
            ->willReturn(true);

        $helper = new AcceptHelper($auth, false, $role);

        $parentPage = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentPage->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(false);
        $parentPage->expects(self::never())
            ->method('hashCode');
        $parentPage->expects(self::never())
            ->method('getOrder');
        $parentPage->expects(self::never())
            ->method('setParent');

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);
        $page->expects(self::once())
            ->method('getPrivilege')
            ->willReturn(null);
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::never())
            ->method('hashCode');
        $page->expects(self::never())
            ->method('getOrder');
        $page->expects(self::never())
            ->method('setParent');

        self::assertFalse($helper->accept($page));
    }

    /** @throws Exception */
    public function testDoNotAcceptByAuthorizationWithParent5(): void
    {
        $role     = 'testRole';
        $resource = 'testResource';

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::once())
            ->method('isGranted')
            ->with($role, $resource, null)
            ->willReturn(true);

        $helper = new AcceptHelper($auth, false, $role);

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);
        $page->expects(self::once())
            ->method('getPrivilege')
            ->willReturn(null);
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::never())
            ->method('hashCode');
        $page->expects(self::never())
            ->method('getOrder');
        $page->expects(self::never())
            ->method('setParent');

        self::assertTrue($helper->accept($page, false));
    }

    /** @throws Exception */
    public function testDoNotAcceptByAuthorizationWithParent6(): void
    {
        $role      = 'testRole';
        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::once())
            ->method('isGranted')
            ->with($role, $resource, $privilege)
            ->willReturn(true);

        $helper = new AcceptHelper($auth, false, $role);

        $parentPage = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);
        $page->expects(self::once())
            ->method('getPrivilege')
            ->willReturn($privilege);
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::never())
            ->method('hashCode');
        $page->expects(self::never())
            ->method('getOrder');
        $page->expects(self::never())
            ->method('setParent');

        self::assertTrue($helper->accept($page));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testDoNotAcceptInvisibleParent(): void
    {
        $parentPage = new Uri();
        $parentPage->setUri('page2/page2_3/page2_3_2');
        $parentPage->setVisible('0');

        $page = new Uri();
        $page->setUri('page2/page2_3/page2_3_2/1');
        $page->setActive('1');

        $parentPage->addPage($page);

        $role = 'testRole';

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $helper = new AcceptHelper($auth, false, $role);

        self::assertFalse($helper->accept($page));
    }

    /** @throws Exception */
    public function testDoNotAcceptByAuthorizationWithParent7(): void
    {
        $role       = 'testRole';
        $resourceId = 'testResource';
        $privilege  = 'testPrivilege';

        $resource = $this->getMockBuilder(ResourceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resource->expects(self::once())
            ->method('getResourceId')
            ->willReturn($resourceId);

        $auth = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::once())
            ->method('isAllowed')
            ->with($role, $resourceId, $privilege)
            ->willReturn(true);

        $helper = new AcceptHelper($auth, false, $role);

        $parentPage = $this->getMockBuilder(AbstractContainer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);
        $page->expects(self::once())
            ->method('getPrivilege')
            ->willReturn($privilege);
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::never())
            ->method('getOrder');
        $page->expects(self::never())
            ->method('setParent');

        self::assertTrue($helper->accept($page));
    }

    /** @throws Exception */
    public function testDoNotAcceptByAuthorizationWithParent8(): void
    {
        $role     = 'testRole';
        $resource = 'testResource';

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::once())
            ->method('isGranted')
            ->with($role, $resource, null)
            ->willReturn(true);

        $helper = new AcceptHelper($auth, false, $role);

        $parentPage = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentPage->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(false);
        $parentPage->expects(self::never())
            ->method('getOrder');
        $parentPage->expects(self::never())
            ->method('setParent');

        $page = $this->getMockBuilder(AbstractPage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);
        $page->expects(self::once())
            ->method('getPrivilege')
            ->willReturn(null);
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::never())
            ->method('getOrder');
        $page->expects(self::never())
            ->method('setParent');

        self::assertFalse($helper->accept($page));
    }
}
