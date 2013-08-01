<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractEntity;
use Oro\Bundle\TestFrameworkBundle\Pages\Entity;

class Group extends AbstractEntity implements Entity
{

    protected $name;

    protected $roles;

    public function __construct($testCase, $redirect = true)
    {
        parent::__construct($testCase, $redirect);
        $this->name = $this->byId('oro_user_group_form_name');
        $this->roles = $this->select($this->byId('oro_user_group_form_roles'));
    }

    public function setName($name)
    {
        $this->name->value($name);
        return $this;
    }

    public function getName()
    {
        return $this->name->value();
    }

    public function setRoles($roles = array())
    {
        foreach ($roles as $role) {
            $this->roles->selectOptionByLabel($role);
        }

        return $this;
    }
}
