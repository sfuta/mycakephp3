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
        $this->set('items', $this->PaginatorForPdo->paginateForPdo('select * from sandbox', $options));
        // $this->set('items', ConnectionManager::get('default')->execute('select * from sandbox')->fetchAll('assoc'));
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
}
