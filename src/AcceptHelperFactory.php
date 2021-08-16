<?php
/**
 * This file is part of the mimmi20/navigation-helper-acceptpage package.
 *
 * Copyright (c) 2021, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\NavigationHelper\Accept;

use Interop\Container\ContainerInterface;
use Laminas\Permissions\Acl\Acl;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mezzio\GenericAuthorization\AuthorizationInterface;

use function array_key_exists;
use function is_array;
use function is_string;

final class AcceptHelperFactory implements FactoryInterface
{
    /**
     * Create and return a navigation view helper instance.
     *
     * @param string            $requestedName
     * @param array<mixed>|null $options
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): AcceptHelper
    {
        $authorization   = null;
        $renderInvisible = false;
        $role            = null;

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

            if (
                array_key_exists('role', $options)
                && is_string($options['role'])
            ) {
                $role = $options['role'];
            }
        }

        return new AcceptHelper($authorization, $renderInvisible, $role);
    }
}
