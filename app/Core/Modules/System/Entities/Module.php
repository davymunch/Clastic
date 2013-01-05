<?php

namespace Core\Modules\System\Entities;

/**
 * @Entity
 */
class Module
{
    /**
     * @Id @GeneratedValue @Column(type="integer")
     */
    protected $id;

    /**
     * @Column(type="text")
     */
    protected $name;
}