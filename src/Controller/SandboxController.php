<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;

class SandboxController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Paginator');
        $this->loadComponent('PaginatorForPdo');
    }

    public function index()
    {
        $options = [
            'limit' => 10,
            'order' => [
                'id' => 'desc'
            ]
        ];
        $this->PaginatorForPdo->setSortColumns(['id', 'tname']);
        $this->set('items',
            $this->PaginatorForPdo->paginateForPdo('select s.id, s.tname from sandbox s where id > ?', [20], $options)
        );
    }

    public function index2()
    {
        $this->paginate = [
            'limit' => 10,
            'order' => [
                'p_id' => 'desc'
            ]
        ];
        $this->set('pageItems', $this->paginate($query = TableRegistry::get('PageSandbox')->find()));
    }

    public function dump()
    {
        debug(Configure::read('FixInfo'));
        debug(Configure::read('Info'));
        die;
    }
}
