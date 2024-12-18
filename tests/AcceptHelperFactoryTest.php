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

namespace Mimmi20Test\NavigationHelper\Accept;

use Laminas\Permissions\Acl\Acl;
use Mimmi20\Mezzio\GenericAuthorization\AuthorizationInterface;
use Mimmi20\NavigationHelper\Accept\AcceptHelper;
use Mimmi20\NavigationHelper\Accept\AcceptHelperFactory;
use Override;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function assert;

final class AcceptHelperFactoryTest extends TestCase
{
    private AcceptHelperFactory $factory;

    /** @throws void */
    #[Override]
    protected function setUp(): void
    {
        $this->factory = new AcceptHelperFactory();
    }

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     */
    public function testInvocationWithoutOptions(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('get');

        assert($container instanceof ContainerInterface);
        $helper = ($this->factory)($container, '');

        self::assertInstanceOf(AcceptHelper::class, $helper);

        self::assertNull($helper->getAuthorization());
        self::assertSame([], $helper->getRoles());
        self::assertFalse($helper->getRenderInvisible());
    }

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     */
    public function testInvocationWithOptions(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('get');

        $auth            = $this->createMock(AuthorizationInterface::class);
        $renderInvisible = true;
        $roles           = ['test-role'];

        assert($container instanceof ContainerInterface);
        $helper = ($this->factory)(
            $container,
            '',
            [
                'authorization' => $auth,
                'renderInvisible' => $renderInvisible,
                'roles' => $roles,
            ],
        );

        self::assertInstanceOf(AcceptHelper::class, $helper);

        self::assertSame($auth, $helper->getAuthorization());
        self::assertSame($roles, $helper->getRoles());
        self::assertTrue($helper->getRenderInvisible());
    }

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     */
    public function testInvocationWithOptions2(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('get');

        $auth            = 'invalid-auth';
        $renderInvisible = '1';
        $roles           = [];

        assert($container instanceof ContainerInterface);
        $helper = ($this->factory)(
            $container,
            '',
            [
                'authorization' => $auth,
                'renderInvisible' => $renderInvisible,
                'roles' => $roles,
            ],
        );

        self::assertInstanceOf(AcceptHelper::class, $helper);

        self::assertNull($helper->getAuthorization());
        self::assertSame($roles, $helper->getRoles());
        self::assertTrue($helper->getRenderInvisible());
    }

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     */
    public function testInvocationWithOptions3(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('get');

        $auth            = $this->createMock(Acl::class);
        $renderInvisible = true;
        $roles           = ['test-role'];

        assert($container instanceof ContainerInterface);
        $helper = ($this->factory)(
            $container,
            '',
            [
                'authorization' => $auth,
                'renderInvisible' => $renderInvisible,
                'roles' => $roles,
            ],
        );

        self::assertInstanceOf(AcceptHelper::class, $helper);

        self::assertSame($auth, $helper->getAuthorization());
        self::assertSame($roles, $helper->getRoles());
        self::assertTrue($helper->getRenderInvisible());
    }

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     */
    public function testInvocationWithOptionsRuleNotString(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('get');

        $auth            = 'invalid-auth';
        $renderInvisible = '1';
        $role            = [];

        assert($container instanceof ContainerInterface);
        $helper = ($this->factory)(
            $container,
            '',
            [
                'authorization' => $auth,
                'renderInvisible' => $renderInvisible,
                'role' => $role,
            ],
        );

        self::assertInstanceOf(AcceptHelper::class, $helper);

        self::assertNull($helper->getAuthorization());
        self::assertSame([], $helper->getRoles());
        self::assertTrue($helper->getRenderInvisible());
    }
}
