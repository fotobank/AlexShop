<?php

/**
 * Simpla CMS
 * @copyright     2011 Denis Pikusov
 * @link          http://simplacms.ru
 * @author        Denis Pikusov
 */

require_once('Simpla.php');

/**
 * Class Users
 */
class Users extends Simpla
{
    // осторожно, при изменении соли испортятся текущие пароли пользователей
    /**
     * @var string
     */
    private $salt = '8e86a279d6e182b3c811c559e6b15484';

    /**
     * @param array $filter
     *
     * @return array|bool
     */
    public function get_users(array $filter = [])
    {
        $limit = 1000;
        $page = 1;
        $group_id_filter = '';
        $keyword_filter = '';

        if (isset($filter['limit']))
            $limit = max(1, (int)$filter['limit']);

        if (isset($filter['page']))
            $page = max(1, (int)$filter['page']);

        if (isset($filter['group_id']))
            $group_id_filter = $this->db->placehold('AND u.group_id in(?@)', (array)$filter['group_id']);

        if (isset($filter['keyword'])){
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword)
                $keyword_filter .= $this->db->placehold('AND (u.name LIKE "%' . $this->db->escape(trim($keyword)) . '%" OR u.email LIKE "%' . $this->db->escape(trim($keyword)) . '%"  OR u.last_ip LIKE "%' . $this->db->escape(trim($keyword)) . '%")');
        }

        $order = 'u.name';
        if (!empty($filter['sort']))
            switch ($filter['sort']) {
                case 'date':
                    $order = 'u.created DESC';
                    break;
                case 'name':
                    $order = 'u.name';
                    break;
            }


        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);
        // Выбираем пользователей
        $query = $this->db->placehold("SELECT u.id, u.email, u.password, u.name, u.group_id, u.enabled, u.last_ip, u.created, g.discount, g.name as group_name FROM __users u
		                                LEFT JOIN __groups g ON u.group_id=g.id 
										WHERE 1 $group_id_filter $keyword_filter ORDER BY $order $sql_limit");
        $this->db->query($query);

        return $this->db->results();
    }

    /**
     * @param array $filter
     *
     * @return bool|int
     */
    public function count_users(array $filter = [])
    {
        $group_id_filter = '';
        $keyword_filter = '';

        if (isset($filter['group_id']))
            $group_id_filter = $this->db->placehold('AND u.group_id in(?@)', (array)$filter['group_id']);

        if (isset($filter['keyword'])){
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword)
                $keyword_filter .= $this->db->placehold('AND u.name LIKE "%' . $this->db->escape(trim($keyword)) . '%" OR u.email LIKE "%' . $this->db->escape(trim($keyword)) . '%"');
        }

        // Выбираем пользователей
        $query = $this->db->placehold("SELECT count(*) as count FROM __users u
		                                LEFT JOIN __groups g ON u.group_id=g.id 
										WHERE 1 $group_id_filter $keyword_filter ORDER BY u.name");
        $this->db->query($query);

        return $this->db->result('count');
    }

    /**
     * @param $id
     *
     * @return bool|int
     */
    public function get_user($id)
    {
        if (gettype($id) == 'string')
            $where = $this->db->placehold(' WHERE u.email=? ', $id);
        else
            $where = $this->db->placehold(' WHERE u.id=? ', (int)$id);

        // Выбираем пользователя
        $query = $this->db->placehold("SELECT u.id, u.email, u.password, u.name, u.group_id, u.enabled, u.last_ip, u.created, g.discount, g.name as group_name FROM __users u LEFT JOIN __groups g ON u.group_id=g.id $where LIMIT 1", $id);
        $this->db->query($query);
        $user = $this->db->result();
        if (empty($user))
            return false;
        $user->discount *= 1; // Убираем лишние нули, чтобы было 5 вместо 5.00
        return $user;
    }

    /**
     * @param $user
     *
     * @return bool
     */
    public function add_user($user)
    {
        $user = (array)$user;
        if (isset($user['password']))
            $user['password'] = md5($this->salt . $user['password'] . md5($user['password']));

        $query = $this->db->placehold('SELECT count(*) AS count FROM __users WHERE email=?', $user['email']);
        $this->db->query($query);

        if ($this->db->result('count') > 0)
            return false;

        $query = $this->db->placehold('INSERT INTO __users SET ?%', $user);
        $this->db->query($query);

        return $this->db->insert_id();
    }

    /**
     * @param $id
     * @param $user
     *
     * @return mixed
     */
    public function update_user($id, $user)
    {
        $user = (array)$user;
        if (isset($user['password']))
            $user['password'] = md5($this->salt . $user['password'] . md5($user['password']));
        $query = $this->db->placehold('UPDATE __users SET ?% WHERE id=? LIMIT 1', $user, (int)$id);
        $this->db->query($query);

        return $id;
    }

    /*
    *
    * Удалить пользователя
    * @param $post
    *
    */
    /**
     * @param $id
     *
     * @return bool
     */
    public function delete_user($id)
    {
        if (!empty($id)){
            $query = $this->db->placehold('UPDATE __orders SET user_id=NULL WHERE id=? LIMIT 1', (int)$id);
            $this->db->query($query);

            $query = $this->db->placehold('DELETE FROM __users WHERE id=? LIMIT 1', (int)$id);
            if ($this->db->query($query))
                return true;
        }

        return false;
    }

    /**
     * @return array|bool
     */
    public function get_groups()
    {
        // Выбираем группы
        $query = $this->db->placehold('SELECT g.id, g.name, g.discount FROM __groups AS g ORDER BY g.discount');
        $this->db->query($query);

        return $this->db->results();
    }

    /**
     * @param $id
     *
     * @return bool|int
     */
    public function get_group($id)
    {
        // Выбираем группу
        $query = $this->db->placehold('SELECT * FROM __groups WHERE id=? LIMIT 1', $id);
        $this->db->query($query);

        return $this->db->result();
    }


    /**
     * @param $group
     *
     * @return mixed
     */
    public function add_group($group)
    {
        $query = $this->db->placehold('INSERT INTO __groups SET ?%', $group);
        $this->db->query($query);

        return $this->db->insert_id();
    }

    /**
     * @param $id
     * @param $group
     *
     * @return mixed
     */
    public function update_group($id, $group)
    {
        $query = $this->db->placehold("UPDATE __groups SET ?% WHERE id=? LIMIT 1", $group, (int)$id);
        $this->db->query($query);

        return $id;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function delete_group($id)
    {
        if (!empty($id)){
            $query = $this->db->placehold("UPDATE __users SET group_id=NULL WHERE group_id=? LIMIT 1", (int)($id));
            $this->db->query($query);

            $query = $this->db->placehold("DELETE FROM __groups WHERE id=? LIMIT 1", (int)($id));
            if ($this->db->query($query))
                return true;
        }

        return false;
    }

    /**
     * @param $email
     * @param $password
     *
     * @return bool|int
     */
    public function check_password($email, $password)
    {
        $encpassword = md5($this->salt . $password . md5($password));
        $query = $this->db->placehold('SELECT id FROM __users WHERE email=? AND password=? LIMIT 1', $email, $encpassword);
        $this->db->query($query);
        if ($id = $this->db->result('id'))
            return $id;

        return false;
    }

}