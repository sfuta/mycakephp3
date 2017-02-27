<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         2.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller\Component;

use Cake\Controller\Component\PaginatorComponent;
use Cake\Network\Exception\NotFoundException;
use Cake\Utility\Hash;
use Cake\Datasource\ConnectionManager;

class PaginatorForPdoComponent extends PaginatorComponent
{

    public function paginateForPdo($sqlString, array $options = [])
    {
        // $options = $this->validateSort($object, $options);
        // $options = $this->checkLimit($options);
        $alias = 'Pager';
        $request = $this->_registry->getController()->request;
        $queryParam = $request->getQueryParams();
        $params = [];
        if (isset($queryParam['sort'])) {
            $params['order'] = [
                $queryParam['sort'] => $queryParam['direction'] ?? 'asc'
            ];
        }
        $params['page'] = $queryParam['page'] ?? 1;
        $options = array_merge($options, $params);

        $options += ['page' => 1, 'scope' => null];
        $options['page'] = (int)$options['page'] < 1 ? 1 : (int)$options['page'];
        $limit = $options['limit'];
        $page = $options['page'];

        $countSql = sprintf('select count(*) `count` from (%s) count_table', $sqlString);
        $limitPart = " LIMIT $limit OFFSET " . (($page - 1) * $limit);
        if ($options['order']) {
            $orderPart = ' ORDER BY ' . key($options['order']) . ' ' . current($options['order']);
        } else {
            $orderPart = '';
        }
        $pageSql = $sqlString . $orderPart . $limitPart;
        $results = ConnectionManager::get('default')->execute($pageSql)->fetchAll('assoc') ?? [];
        $count   = ConnectionManager::get('default')->execute($countSql)->fetch('assoc')['count'] ?? 0;
        $numResults = count($results);

        $pageCount = (int)ceil($count / $limit);
        $requestedPage = $page;
        $page = max(min($page, $pageCount), 1);

        $order = (array)$options['order'];
        $sortDefault = $directionDefault = false;
        if (!empty($options['default_order']) && count($options['default_order']) == 1) {
            $sortDefault = key($options['default_order']);
            $directionDefault = current($options['default_order']);
        }

        $paging = [
            'finder' => '',
            'page' => $page,
            'current' => $numResults,
            'count' => $count,
            'perPage' => $limit,
            'prevPage' => ($page > 1),
            'nextPage' => ($count > ($page * $limit)),
            'pageCount' => $pageCount,
            'sort' => key($order),
            'direction' => current($order),
            'limit' => null,
            'sortDefault' => $sortDefault,
            'directionDefault' => $directionDefault,
            'scope' => null,
        ];

        if (!$request->getParam('paging')) {
            $request->params['paging'] = [];
        }
        $request->params['paging'] = [$alias => $paging] + (array)$request->getParam('paging');

        if ($requestedPage > $page) {
            throw new NotFoundException();
        }

        return $results;
    }

    /**
     * Merges the various options that Pagination uses.
     * Pulls settings together from the following places:
     *
     * - General pagination settings
     * - Model specific settings.
     * - Request parameters
     *
     * The result of this method is the aggregate of all the option sets combined together. You can change
     * config value `whitelist` to modify which options/values can be set using request parameters.
     *
     * @param string $alias Model alias being paginated, if the general settings has a key with this value
     *   that key's settings will be used for pagination instead of the general ones.
     * @param array $settings The settings to merge with the request data.
     * @return array Array of merged options.
     */
    public function mergeOptions($alias, $settings)
    {
        $defaults = $this->getDefaults($alias, $settings);
        $request = $this->_registry->getController()->request;
        $scope = Hash::get($settings, 'scope', null);
        $query = $request->getQueryParams();
        if ($scope) {
            $query = Hash::get($request->getQueryParams(), $scope, []);
        }
        $request = array_intersect_key($query, array_flip($this->_config['whitelist']));

        return array_merge($defaults, $request);
    }
}
