<?php

namespace Controller;

use Model\Project as ProjectModel;
use Model\SubTask;

/**
 * Application controller
 *
 * @package  controller
 * @author   Frederic Guillot
 */
class App extends Base
{
    /**
     * Dashboard for the current user
     *
     * @access public
     */
    public function index()
    {
        $paginate = $this->request->getStringParam('paginate', 'userTasks');
        $offset = $this->request->getIntegerParam('offset', 0);
        $direction = $this->request->getStringParam('direction');
        $order = $this->request->getStringParam('order');

        $user_id = $this->acl->getUserId();
        $projects = $this->projectPermission->getMemberProjects($user_id);
        $project_ids = array_keys($projects);

        $params = array(
            'title' => t('Dashboard'),
            'board_selector' => $this->projectPermission->getAllowedProjects($user_id),
            'events' => $this->projectActivity->getProjects($project_ids, 10),
        );

        $params += $this->getTaskPagination($user_id, $paginate, $offset, $order, $direction);
        $params += $this->getSubtaskPagination($user_id, $paginate, $offset, $order, $direction);
        $params += $this->getProjectPagination($project_ids, $paginate, $offset, $order, $direction);

        $this->response->html($this->template->layout('app/dashboard', $params));
    }

    /**
     * Get tasks pagination
     *
     * @access public
     */
    private function getTaskPagination($user_id, $paginate, $offset, $order, $direction)
    {
        $limit = 10;

        if (! in_array($order, array('tasks.id', 'project_name', 'title'))) {
            $order = 'tasks.id';
            $direction = 'ASC';
        }

        if ($paginate === 'userTasks') {
            $tasks = $this->taskPaginator->userTasks($user_id, $offset, $limit, $order, $direction);
        }
        else {
            $offset = 0;
            $tasks = $this->taskPaginator->userTasks($user_id, $offset, $limit);
        }

        return array(
            'tasks' => $tasks,
            'task_pagination' => array(
                'controller' => 'app',
                'action' => 'index',
                'params' => array('paginate' => 'userTasks'),
                'direction' => $direction,
                'order' => $order,
                'total' => $this->taskPaginator->countUserTasks($user_id),
                'offset' => $offset,
                'limit' => $limit,
            )
        );
    }

    /**
     * Get subtasks pagination
     *
     * @access public
     */
    private function getSubtaskPagination($user_id, $paginate, $offset, $order, $direction)
    {
        $status = array(SubTask::STATUS_TODO, SubTask::STATUS_INPROGRESS);
        $limit = 10;

        if (! in_array($order, array('tasks.id', 'project_name', 'status', 'title'))) {
            $order = 'tasks.id';
            $direction = 'ASC';
        }

        if ($paginate === 'userSubtasks') {
            $subtasks = $this->subtaskPaginator->userSubtasks($user_id, $status, $offset, $limit, $order, $direction);
        }
        else {
            $offset = 0;
            $subtasks = $this->subtaskPaginator->userSubtasks($user_id, $status, $offset, $limit);
        }

        return array(
            'subtasks' => $subtasks,
            'subtask_pagination' => array(
                'controller' => 'app',
                'action' => 'index',
                'params' => array('paginate' => 'userSubtasks'),
                'direction' => $direction,
                'order' => $order,
                'total' => $this->subtaskPaginator->countUserSubtasks($user_id, $status),
                'offset' => $offset,
                'limit' => $limit,
            )
        );
    }

    /**
     * Get projects pagination
     *
     * @access public
     */
    private function getProjectPagination($project_ids, $paginate, $offset, $order, $direction)
    {
        $limit = 5;

        if (! in_array($order, array('id', 'name'))) {
            $order = 'name';
            $direction = 'ASC';
        }

        if ($paginate === 'projectSummaries') {
            $projects = $this->projectPaginator->projectSummaries($project_ids, $offset, $limit, $order, $direction);
        }
        else {
            $offset = 0;
            $projects = $this->projectPaginator->projectSummaries($project_ids, $offset, $limit);
        }

        return array(
            'projects' => $projects,
            'project_pagination' => array(
                'controller' => 'app',
                'action' => 'index',
                'params' => array('paginate' => 'projectSummaries'),
                'direction' => $direction,
                'order' => $order,
                'total' => count($project_ids),
                'offset' => $offset,
                'limit' => $limit,
            )
        );
    }
}
