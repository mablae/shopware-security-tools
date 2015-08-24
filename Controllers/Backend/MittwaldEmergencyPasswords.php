<?php
use Shopware\CustomModels\MittwaldSecurityTools\EmergencyPassword;


/**
 * standard controller for the emergency password model
 *
 * Class Shopware_Controllers_Backend_MittwaldEmergencyPasswords
 *
 *
 * Copyright (C) 2015 Philipp Mahlow, Mittwald CM-Service GmbH & Co.KG
 *
 * This plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program (see LICENSE.txt). If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @author Philipp Mahlow <p.mahlow@mittwald.de>
 *
 */
class Shopware_Controllers_Backend_MittwaldEmergencyPasswords extends Shopware_Controllers_Backend_Application
{

    /**
     * @var string
     */
    protected $model = 'Shopware\CustomModels\MittwaldSecurityTools\EmergencyPassword';

    /**
     * @var string
     */
    protected $alias = 'emergencyPassword';

    /**
     * Controller action which can be called over an ajax request.
     * This function is normally used for backend listings.
     * The listing will be selected over the getList function.
     *
     * The function expects the following request parameter:
     *  query - Search string which inserted in the search field.
     *  association - Doctrine property name of the association
     *  start - Pagination start value
     *  limit - Pagination limit value
     */
    public function listAction()
    {
        $this->View()->assign(
            $this->getList(
                $this->Request()->getParam('start', 0),
                $this->Request()->getParam('limit', 20),
                $this->Request()->getParam('sort', array()),
                $this->Request()->getParam('filter', array()),
                $this->Request()->getParams(),
                $this->Request()->getParam('userID', NULL)
            )
        );
    }

    /**
     * gets the emergency password list for the given user as CSV
     */
    public function listCSVAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer(FALSE);
        $this->Response()->setHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->Response()->setHeader('Content-Disposition', 'attachment;filename=emergency_passwords.csv');


        $userID = $this->Request()->getParam('userID', NULL);

        $builder = $this->getMyQueryBuilder(0, 20, array(), array(), $userID);
        $paginator = $this->getQueryPaginator($builder, \Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);
        /**
         * @var EmergencyPassword[] $data
         */
        $data = $paginator->getIterator()->getArrayCopy();

        $userRepository = $this->getManager()->getRepository('\Shopware\Models\User\User');

        /**
         * @var \Shopware\Models\User\User $user
         */
        $user = $userRepository->find($userID);

        //use this to set the BOM to show it in the right way for excel and stuff
        echo "\xEF\xBB\xBF";
        $fp = fopen('php://output', 'w');
        fputcsv($fp, array('Benutzername', 'Notfallpasswort'), ";");


        if ($user !== NULL) {
            foreach ($data as $entry) {
                fputcsv($fp, array(
                    $user->getUsername(),
                    $entry->getPassword()
                ), ";");
            }
        }
        fclose($fp);
    }

    /**
     * The getList function returns an array of the configured class model.
     * The listing query created in the getListQuery function.
     * The pagination of the listing is handled inside this function.
     *
     * @param int $offset
     * @param int $limit
     * @param array $sort Contains an array of Ext JS sort conditions
     * @param array $filter Contains an array of Ext JS filters
     * @param array $wholeParams Contains all passed request parameters
     * @param int $userID
     * @return array
     */
    protected function getList($offset, $limit, $sort = array(), $filter = array(), array $wholeParams = array(), $userID = NULL)
    {
        if ($userID === NULL) {
            return array('success' => FALSE, 'data' => [], 'total' => 0);
        }

        $builder = $this->getMyQueryBuilder($offset, $limit, $sort, $filter, $userID);

        $paginator = $this->getQueryPaginator($builder);
        $data = $paginator->getIterator()->getArrayCopy();
        $count = $paginator->count();

        return array('success' => TRUE, 'data' => $data, 'total' => $count);
    }

    /**
     * generates an random password
     *
     * @return string
     */
    protected function getPassword($length = 16)
    {
        $pool = '';

        for ($i = 1; $i < $length; $i++) {
            $pool .= '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }

        return substr(str_shuffle($pool), 0, $length);
    }

    /**
     * this will create 10 random emergency passwords for given user.
     */
    public function createAction()
    {
        $userID = $this->Request()->getParam('userID', NULL);

        $userRepository = $this->getManager()->getRepository('\Shopware\Models\User\User');

        /**
         * @var \Shopware\Models\User\User $user
         */
        $user = $userRepository->find($userID);

        if ($user !== NULL) {
            $builder = $this->getMyQueryBuilder(0, 10, array(), array(), $userID);
            $paginator = $this->getQueryPaginator($builder, \Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);
            $data = $paginator->getIterator()->getArrayCopy();

            foreach ($data as $model) {
                $this->manager->remove($model);
            }

            for ($i = 0; $i < 10; $i++) {
                $emergencyPassword = new EmergencyPassword();
                $emergencyPassword->setCreated(new DateTime());
                $emergencyPassword->setIsUsed(FALSE);
                $emergencyPassword->setPassword($this->getPassword());
                $emergencyPassword->setUser($user);
                $this->manager->persist($emergencyPassword);
            }

            $this->manager->flush();
        }
    }

    /**
     * delete is not allowed. will be done automatically.
     */
    public function deleteAction()
    {
        throw new Exception('delete is not allowed');
    }

    /**
     * update is not allowed. will be done automatically.
     */
    public function updateAction()
    {
        throw new Exception('update is not allowed');
    }

    /**
     * @param $offset
     * @param $limit
     * @param $sort
     * @param $filter
     * @param $userID
     * @return \Shopware\Components\Model\QueryBuilder
     */
    protected function getMyQueryBuilder($offset, $limit, $sort, $filter, $userID)
    {
        $builder = $this->getListQuery();

        $builder->leftJoin('emergencyPassword.user', 'user');
        $builder->where('user.id = ' . intval($userID));
        $builder->andWhere('emergencyPassword.isUsed = FALSE');

        $builder->setFirstResult($offset)
            ->setMaxResults($limit);

        $filter = $this->getFilterConditions(
            $filter,
            $this->model,
            $this->alias,
            $this->filterFields
        );

        $sort = $this->getSortConditions(
            $sort,
            $this->model,
            $this->alias,
            $this->sortFields
        );

        if (!empty($sort)) {
            $builder->addOrderBy($sort);
        }

        if (!empty($filter)) {
            $builder->addFilter($filter);
            return $builder;
        }
        return $builder;
    }


}