<?php

namespace Core\Modules\System\Entities;

/**
 * @Entity
 */
class Site
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