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

use Laminas\Permissions\Acl\Acl;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mimmi20\Mezzio\GenericAuthorization\AuthorizationInterface;
use Override;
use Psr\Container\ContainerInterface;

use function array_key_exists;
use function is_array;

final class AcceptHelperFactory implements FactoryInterface
{
    /**
     * Create and return a navigation view helper instance.
     *
     * @param array<mixed>|null $options
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    #[Override]
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array | null $options = null,
    ): AcceptHelper {
        $authorization   = null;
        $renderInvisible = false;
        $roles           = [];

        if (is_array($options)) {
            if (
                array_key_exists('authorization', $options) && (
                    $options['authorization'] instanceof AuthorizationInterface
                    || $options['authorization'] instanceof Acl
                )
            ) {
                $authorization = $options['authorization'];
            }

            if (array_key_exists('renderInvisible', $options)) {
                $renderInvisible = (bool) $options['renderInvisible'];
            }

            if (array_key_exists('roles', $options) && is_array($options['roles'])) {
                $roles = $options['roles'];
            }
        }

        return new AcceptHelper($authorization, $renderInvisible, $roles);
    }
}
