<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Network\Exception\NotFoundException;
use Cake\Utility\Hash;
use Cake\Datasource\ConnectionManager;

class PaginatorForPdoComponent extends Component
{
    private $_sortColumns = [];

    /**
     * ページのソートに使用するカラム名配列を設定
     *
     * @param $sortColumns sortのカラム名からなる配列
     *
     */
    public function setSortColumns(array $sortColumns)
    {
        $this->_sortColumns = $sortColumns;
    }

    /**
     * ページングしたSQL結果を返す
     *
     * @param $sqlString 実行SQL文字列
     * @param $sqlParams 実行SQLにバインドするパラメータ配列
     * @param $options  Paginator設定値
     *
     * @return array  SQL実行結果
     */
    public function paginateForPdo($sqlString, array $sqlParams, array $options)
    {
        $alias = 'Pager';
        $request = $this->_registry->getController()->request;
        $queryParam = $request->getQueryParams();
        $params = [];
        if (isset($queryParam['sort'])) {
            $params['order'] = $this->_validateSort($queryParam);
        }
        $params['page'] = $queryParam['page'] ?? 1;
        $options = array_merge($options, $params);

        $options += ['page' => 1, 'scope' => null];
        $options['page'] = (int)$options['page'] < 1 ? 1 : (int)$options['page'];
        $limit = (int)$options['limit'];
        $page = $options['page'];

        $countSql = sprintf('select count(*) `count` from (%s) count_table', $sqlString);
        $limitPart = " LIMIT $limit OFFSET " . (($page - 1) * $limit);
        if ($options['order']) {
            $orderPart = ' ORDER BY ' . key($options['order']) . ' ' . current($options['order']);
        } else {
            $orderPart = '';
        }
        $pageSql = $sqlString . $orderPart . $limitPart;
        $results = ConnectionManager::get('default')->execute($pageSql, $sqlParams)->fetchAll('assoc') ?? [];
        $count   = ConnectionManager::get('default')->execute($countSql, $sqlParams)->fetch('assoc')['count'] ?? 0;
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

    private function _validateSort($params)
    {
        $direction  = strtolower($params['direction']) ?? 'asc';
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $sortColumn = $params['sort'];
        if (in_array($sortColumn, $this->_sortColumns, true)) {
            return [$sortColumn => $direction];
        }

        return [];
    }

}
