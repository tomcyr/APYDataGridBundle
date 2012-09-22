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

class LanguageColumn extends IntlColumn
{
    public function __initialize(array $params)
    {
        parent::__initialize($params);

        $this->setValues($this->getParam('values', \Symfony\Component\Locale\Locale::getDisplayLanguages(\Locale::getDefault())));
    }

    public function getType()
    {
        return 'language';
    }
}
