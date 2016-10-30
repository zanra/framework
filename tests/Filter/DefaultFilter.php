<?php

/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Filter;

/**
 * Zanra Filter
 *
 * @author Targalis
 *
 */
class DefaultFilter
{
    /**
     * helper.
     *
     * @param Application $application
     */
    public function helper($application)
    {
        echo "Filter using example: \n";
        echo "Your default language is " . $application->getResources()->application->{"default.locale"};
        echo "\n\n";
    }
}
