<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Abhoryo <abhoryo@free.fr>
 * (c) Stanislav Turza
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace APY\DataGridBundle\Grid\Column;

abstract class IntlColumn extends TextColumn
{
    public function __initialize(array $params)
    {
        $params['filter'] = 'select';
        $params['selectFrom'] = 'values';
        $params['operators'] = array(
            self::OPERATOR_EQ,
            self::OPERATOR_NEQ,
            self::OPERATOR_LIKE,
            self::OPERATOR_NLIKE,
            self::OPERATOR_ISNULL,
            self::OPERATOR_ISNOTNULL
        );
        $params['defaultOperator'] = self::OPERATOR_LIKE;
        $params['selectMulti'] = true;

        parent::__initialize($params);

        $this->setAlign($this->getParam('align', 'center'));
        $this->setSize($this->getParam('size', '24'));
    }

    public function isQueryValid($query)
    {
        if (parent::isQueryValid($query)) {
            foreach ((array) $query as $element) {
                if (!in_array($element, array_keys($this->values))) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
}
