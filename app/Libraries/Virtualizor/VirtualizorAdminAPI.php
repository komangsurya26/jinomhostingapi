<?php

namespace App\Libraries\Virtualizor;

class VirtualizorAdminAPI
{
    var $key = '';
    var $pass = '';
    var $ip = '';
    var $port = 4085;
    var $protocol = 'https';
    var $error = array();
    /**
     * Contructor
     *
     * @author       Pulkit Gupta
     * @param        string $ip IP of the NODE
     * @param        string $key The API KEY of your NODE
     * @param        string $pass The API Password of your NODE
     * @param        int $port (Optional) The port to connect to. Port 4085 is the default. 4084 is non-SSL
     * @return       NULL
     */
    function __construct($ip, $key, $pass, $port = 4085)
    {
        $this->key = $key;
        $this->pass = $pass;
        $this->ip = $ip;
        $this->port = $port;
        if ($port != 4085) {
            $this->protocol = 'http';
        }
    }

    /**
     * Dumps a variable
     *
     * @author       Pulkit Gupta
     * @param        array $re The Array or any other variable.
     * @return       NULL
     */
    function r($re)
    {
        echo '<pre>';
        print_r($re);
        echo '</pre>';
    }
    /**
     * Unserializes a string
     *
     * @author       Pulkit Gupta
     * @param        string $str The serialized string
     * @return       array The unserialized array on success OR false on failure
     */
    function _unserialize($str)
    {

        $var = @unserialize($str);
        if (empty($var)) {
            preg_match_all('!s:(\d+):"(.*?)";!s', $str, $matches);
            foreach ($matches[2] as $mk => $mv) {
                $tmp_str = 's:' . strlen($mv) . ':"' . $mv . '";';
                $str = str_replace($matches[0][$mk], $tmp_str, $str);
            }
            $var = @unserialize($str);
        }

        //If it is still empty false
        if (empty($var)) {

            return false;
        } else {

            return $var;
        }
    }

    /**
     * Make an API Key
     *
     * @author       Pulkit Gupta
     * @param        string $key An 8 bit random string
     * @param        string $pass The API Password of your NODE
     * @return       string The new APIKEY which will be used to query
     */
    function make_apikey($key, $pass)
    {
        return $key . md5($pass . $key);
    }

    /**
     * Generates a random string for the given length
     *
     * @author       Pulkit Gupta
     * @param        int $length The length of the random string to be generated
     * @return       string The generated random string
     */
    function generateRandStr($length)
    {
        $randstr = "";
        for ($i = 0; $i < $length; $i++) {
            $randnum = mt_rand(0, 61);
            if ($randnum < 10) {
                $randstr .= chr($randnum + 48);
            } elseif ($randnum < 36) {
                $randstr .= chr($randnum + 55);
            } else {
                $randstr .= chr($randnum + 61);
            }
        }
        return strtolower($randstr);
    }

    /**
     * Makes an API request to the server to do a particular task
     *
     * @author       Pulkit Gupta
     * @param        string $path The action you want to do
     * @param        array $post An array of DATA that should be posted
     * @param        array $cookies An array FOR SENDING COOKIES
     * @return       array The unserialized array on success OR false on failure
     */
    function call($path, $data = array(), $post = array(), $cookies = array())
    {

        $key = $this->generateRandStr(8);
        $apikey = $this->make_apikey($key, $this->pass);

        $url = ($this->protocol) . '://' . $this->ip . ':' . $this->port . '/' . $path;
        $url .= (strstr($url, '?') ? '' : '?');
        $url .= '&adminapikey=' . rawurlencode($this->key) . '&adminapipass=' . rawurlencode($this->pass);
        $url .= '&api=serialize&apikey=' . rawurlencode($apikey);

        // Pass some data if there
        if (!empty($data)) {
            $url .= '&apidata=' . rawurlencode(base64_encode(serialize($data)));
        }
        // Set the curl parameters.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        // Time OUT
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

        // Turn off the server and peer verification (TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        // UserAgent
        curl_setopt($ch, CURLOPT_USERAGENT, 'Softaculous');

        // Cookies
        if (!empty($cookies)) {
            curl_setopt($ch, CURLOPT_COOKIESESSION, true);
            curl_setopt($ch, CURLOPT_COOKIE, http_build_query($cookies, '', '; '));
        }

        if (!empty($post)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Get response from the server.
        $resp = curl_exec($ch);
        curl_close($ch);

        // The following line is a method to test
        //if(preg_match('/sync/is', $url)) echo $resp;

        if (empty($resp)) {
            return false;
        }

        $resp = trim($resp);
        $r = @unserialize($resp);

        if (empty($r)) {
            return false;
        }

        // In case API key does not have access to the page at that time it will return fatal error so we need to add that in error
        if (array_key_exists('fatal_error_text', $r) && !empty($r['fatal_error_text'])) {
            $r['error'] = $r['fatal_error_text'];
        }

        return $r;
    }
    /**
     * Create a VPS
     *
     * @author       Pulkit Gupta
     * @param        string $path The action you want to do
     * @param        array $post An array of DATA that should be posted
     * @param        array $cookies An array FOR SENDING COOKIES
     * @return       array The unserialized array on success OR false on failure
     */
    function addippool($post)
    {
        $post['addippool'] = 1;
        $path = 'index.php?act=addippool';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function addips($post)
    {
        $post['submitip'] = 1;
        $path = 'index.php?act=addips';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function addiso($post)
    {
        $path = 'index.php?act=addiso';
        $post['addiso'] = 1;
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function deleteiso($post)
    {
        $path = 'index.php?act=iso';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }
    function addplan($post)
    {
        $post['addplan'] = 1;
        $path = 'index.php?act=addplan';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function mediagroups($page = 1, $reslen = 50, $post = array())
    {
        if (empty($post)) {
            $path = 'index.php?act=mediagroups';
            $ret = $this->call($path, array(), $post);
        } else {
            $path = 'index.php?act=mediagroups&mgid=' . $post['mgid'] . '&mg_name=' . $post['mg_name'] . '&page=' . $page . '&reslen=' . $reslen;
            $ret = $this->call($path, array(), $post);
        }
        return $ret;
    }

    function addserver($post)
    {
        $post['addserver'] = 1;
        $path = 'index.php?act=addserver';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function servergroups($post = 0)
    {
        $path = 'index.php?act=servergroups';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function addtemplate($post)
    {
        $post['addtemplate'] = 1;
        $path = 'index.php?act=addtemplate';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }
    function adduser($post = 0)
    {
        $path = 'index.php?act=adduser';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    /**
     * Create a VPS
     *
     * @author       Pulkit Gupta
     * @param        array $post An array of DATA that should be posted
     * @param        array $cookies An array FOR SENDING COOKIES
     * @return       array The unserialized array on success OR false on failure
     */
    function addvs($post, $cookies = '')
    {
        $path = 'index.php?act=addvs';
        $post = $this->clean_post($post);
        $ret = $this->call($path, '', $post, $cookies);
        return array(
            'title' => $ret['title'],
            'error' => @empty($ret['error']) ? array() : $ret['error'],
            'vs_info' => $ret['newvs'],
            'globals' => $ret['globals']
        );
    }

    /**
     * Create a VPS (V2 Method)
     *
     * @author       Pulkit Gupta
     * @param        array $post An array of DATA that should be posted
     * @param        array $cookies An array FOR SENDING COOKIES
     * @return       array The unserialized array on success OR false on failure
     */
    function addvs_v2($post, $cookies = '')
    {
        $path = 'index.php?act=addvs';
        $post['addvps'] = 1;
        $post['node_select'] = 1;
        $ret = $this->call($path, '', $post, $cookies);
        return array(
            'title' => $ret['title'],
            'error' => @empty($ret['error']) ? array() : $ret['error'],
            'vs_info' => $ret['newvs'],
            'new_keys' => $ret['new_keys'],
            'globals' => $ret['globals'],
            'done' => @empty($ret['done']) ? array() : $ret['done'] 
        );
    }
    function addiprange($post)
    {
        $path = 'index.php?act=addiprange';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function editiprange($post)
    {
        $path = 'index.php?act=editiprange&ipid=' . $post['ipid'];
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function iprange($page = 1, $reslen = 50, $post = [])
    {
        if (empty($post)) {
            $path = 'index.php?act=ipranges&page=' . $page . '&reslen=' . $reslen;
            $ret = $this->call($path, array(), $post);
        } elseif (isset($post['delete'])) {
            $path = 'index.php?act=ipranges';
            $ret = $this->call($path, array(), $post);
        } else {
            $path = 'index.php?act=ipranges&ipsearch=' . $post['ipsearch'] . '&ippoolsearch=' . $post['ippoolsearch'] . '&lockedsearch=' . $post['lockedsearch'] . '&ippid=' . $post['ippid'] .
                '&page=' . $page . '&reslen=' . $reslen;
            $ret = $this->call($path, array(), $post);
        }
        return $ret;
    }

    function addsg($post)
    {
        $post['addsg'] = 1;
        $path = 'index.php?act=addsg';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function editsg($post)
    {
        $post['editsg'] = 1;
        $path = 'index.php?act=editsg&sgid=' . $post['sgid'];
        $ret = $this->call($path, array(), $post);
        return $ret;
    }
    function deletesg($post)
    {
        $path = 'index.php?act=servergroups';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function listbackupplans($page = 1, $reslen = 50, $post = array())
    {
        $path = 'index.php?act=backup_plans&page=' . $page . '&reslen=' . $reslen;
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function addbackupplan($post = array())
    {
        $post['addbackup_plan'] = 1;
        $path = 'index.php?act=addbackup_plan';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function editbackupplan($post = array())
    {
        $post['editbackup_plan'] = 1;
        $path = 'index.php?act=editbackup_plan';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function deletebackupplan($post)
    {
        $path = 'index.php?act=backup_plans';
        $ret = $this->call($path, array(), $post);
        unset($ret['backup_plans']);
        return $ret;
    }

    function backupservers($page = 1, $reslen = 50, $post = array())
    {
        $path = 'index.php?act=backupservers&page=' . $page . '&reslen=' . $reslen;
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function deletebackupservers($post)
    {
        $path = 'index.php?act=backupservers';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }
    function testbackupservers($post)
    {
        $path = 'index.php?act=backupservers';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function addbackupserver($post)
    {
        $post['addbackupserver'] = 1;
        $path = 'index.php?act=addbackupserver';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function editbackupserver($post)
    {
        $post['editbackupserver'] = 1;
        $path = 'index.php?act=editbackupserver&id=' . $post['id'];
        $ret = $this->call($path, array(), $post);

        return $ret;
    }

    function addstorage($post)
    {
        $post['addstorage'] = 1;
        $path = 'index.php?act=addstorage';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function storages($post = array(), $page = 1, $reslen = 50)
    {
        $path = 'index.php?act=storage&page=' . $page . '&reslen=' . $reslen;
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function editstorage($post)
    {
        $post['editstorage'] = 1;
        $path = 'index.php?act=editstorage&stid=' . $post['stid'];
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function orphaneddisks($post = array())
    {
        $path = 'index.php?act=orphaneddisks';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }
    function adddnsplan($post)
    {
        $post['adddnsplan'] = 1;
        $path = 'index.php?act=adddnsplan';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function listdnsplans($page = 1, $reslen = 50, $post = array())
    {
        if (!isset($post['planname'])) {
            $path = 'index.php?act=dnsplans';
            $ret = $this->call($path, array(), $post);
        } else {
            $path = 'index.php?act=dnsplans&planname=' . urlencode($post['planname']) . '&page=' . $page . '&reslen=' . $reslen;
            $ret = $this->call($path, array(), $post);
        }
        return $ret;
    }

    function edit_dnsplans($post = array())
    {
        $post['editdnsplan'] = 1;
        $path = 'index.php?act=editdnsplan&dnsplid=' . $post['dnsplid'];
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function delete_dnsplans($post)
    {
        $path = 'index.php?act=dnsplans';
        $ret = $this->call($path, array(), $post);

        return $ret;
    }

    function add_admin_acl($post)
    {
        $path = 'index.php?act=add_admin_acl';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function admin_acl($post = array())
    {
        if (empty($post)) {
            $path = 'index.php?act=admin_acl';
            $ret = $this->call($path);
        } else {
            $path = 'index.php?act=admin_acl';
            $ret = $this->call($path, array(), $post);
        }
        return $ret;
    }
    function edit_admin_acl($post = array())
    {
        $path = 'index.php?act=edit_admin_acl&aclid=' . $post['aclid'];
        $ret = $this->call($path, array(), $post);
        return $ret;
    }


    function addmg($post)
    {
        $post['addmg'] = 1;
        $path = 'index.php?act=addmg';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function editmg($post)
    {
        $post['editmg'] = 1;
        $path = 'index.php?act=editmg&mgid=' . $post['mgid'];
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function delete_mg($post)
    {
        $path = 'index.php?act=mediagroups&delete=' . $post['delete'];
        $ret = $this->call($path);
        return $ret;
    }

    function add_distro($post)
    {
        $post['add_distro'] = 1;
        $path = 'index.php?act=add_distro';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function edit_distro($post)
    {
        $post['add_distro'] = 1;
        $path = 'index.php?act=add_distro&edit=' . $post['edit'];
        $ret = $this->call($path, array(), $post);
        return $ret;
    }
    function list_distros($post = 0)
    {
        if (empty($post)) {
            $path = 'index.php?act=list_distros';
            $ret = $this->call($path, array(), $post);
        } else {
            $path = 'index.php?act=list_distros&delete=' . $post['delete'];
            $ret = $this->call($path);
        }
        return $ret;
    }

    function list_euiso($page = 1, $reslen = 50, $post = array())
    {
        $path = 'index.php?act=euiso&page=' . $page . '&reslen=' . $reslen;
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function delete_euiso($post)
    {
        $path = 'index.php?act=euiso';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function list_recipes($page = 1, $reslen = 50, $post = array())
    {
        if (!isset($post['rid'])) {
            $path = 'index.php?act=recipes&page=' . $page . '&reslen=' . $reslen;
            $ret = $this->call($path, array(), $post);
        } else {
            $path = 'index.php?act=recipes&rid=' . $post['rid'] . '&rname=' . $post['rname'] . '&page=' . $page . '&reslen=' . $reslen;
            $ret = $this->call($path, array(), $post);
        }
        return $ret;
    }

    function add_recipes($post)
    {
        $post['addrecipe'] = 1;
        $path = 'index.php?act=addrecipe';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function editrecipe($post)
    {
        $post['editrecipe'] = 1;
        $path = 'index.php?act=editrecipe&rid=' . $post['rid'];
        $ret = $this->call($path, array(), $post);
        return $ret;
    }
    // The recipe function deletes activates and deactivates a recipes
    function recipes($post)
    {
        $path = 'index.php?act=recipes';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function tasks($page = 1, $reslen = 50, $post = array())
    {
        if (empty($post) || isset($post['show_logs'])) {
            $path = 'index.php?act=tasks&page=' . $page . '&reslen=' . $reslen;
        } else {
            $param = ['vpsid', 'actid', 'username', 'action', 'status', 'order'];
            $params = ''; // Inisialisasi sebelum digunakan
            foreach ($param as $k => $v) {
                if (!empty($post[$v])) {
                    $params .= '&' . $v . '=' . $post[$v];
                }
            }

            $path = 'index.php?act=tasks&page=' . $page . '&reslen=' . $reslen . $params;
        }
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function addpdns($post)
    {
        $post['addpdns'] = 1;
        $path = 'index.php?act=addpdns';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function adminindex()
    {
        $path = 'index.php?act=adminindex';
        $res = $this->call($path);
        return $res;
    }

    function apidoings() {}

    function backup($post)
    {
        $path = 'index.php?act=backup';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }
    function bandwidth($post = array())
    {
        if (empty($post)) {
            $path = 'index.php?act=bandwidth';
            $ret = $this->call($path);
        } else {
            $path = 'index.php?act=bandwidth&show=' . $post['show'];
            $ret = $this->call($path, array(), $post);
        }
        return $ret;
    }

    /**
     * Cleaning the POST variables
     *
     * @author Pulkit Gupta
     * @param array $post An array of DATA that should be posted
     * @param array $cookies An array FOR SENDING COOKIES
     * @return array The unserialized array on success OR false on failure
     */
    function clean_post(&$post, $edit = 0)
    {
        $post['serid'] = !isset($post['serid']) ? 0 : (int)$post['serid'];
        $post['uid'] = !isset($post['uid']) ? 0 : (int)$post['uid'];
        $post['plid'] = !isset($post['plid']) ? 0 : (int)$post['plid'];
        $post['osid'] = !isset($post['osid']) ? 0 : (int)$post['osid'];
        $post['iso'] = !isset($post['iso']) ? 0 : (int)$post['iso'];
        $post['space'] = !isset($post['space']) ? 10 : $post['space'];
        $post['ram'] = !isset($post['ram']) ? 512 : (int)$post['ram'];
        $post['swapram'] = !isset($post['swapram']) ? 1024 : (int)$post['swapram'];
        $post['bandwidth'] = !isset($post['bandwidth']) ? 0 : (int)$post['bandwidth'];
        $post['network_speed'] = !isset($post['network_speed']) ? 0 : (int)$post['network_speed'];
        $post['cpu'] = !isset($post['cpu']) ? 1000 : (int)$post['cpu'];
        $post['cores'] = !isset($post['cores']) ? 4 : (int)$post['cores'];
        $post['cpu_percent'] = !isset($post['cpu_percent']) ? 100 : (int)$post['cpu_percent'];
        $post['vnc'] = !isset($post['vnc']) ? 1 : (int)$post['vnc'];
        $post['vncpass'] = !isset($post['vncpass']) ? 'test' : $post['vncpass'];
        $post['sec_iso'] = !isset($post['sec_iso']) ? 0 : $post['sec_iso'];
        $post['kvm_cache'] = !isset($post['kvm_cache']) ? 0 : $post['kvm_cache'];
        $post['io_mode'] = !isset($post['io_mode']) ? 0 : $post['io_mode'];
        $post['vnc_keymap'] = !isset($post['vnc_keymap']) ? 'en-us' : $post['vnc_keymap'];
        $post['nic_type'] = !isset($post['nic_type']) ? 'default' : $post['nic_type'];
        $post['osreinstall_limit'] = !isset($post['osreinstall_limit']) ? 0 : (int)$post['osreinstall_limit'];
        $post['mgs'] = !isset($post['mgs']) ? 0 : $post['mgs'];
        $post['tuntap'] = !isset($post['tuntap']) ? 0 : $post['tuntap'];
        $post['virtio'] = !isset($post['virtio']) ? 0 : $post['virtio'];
        if (isset($post['hvm'])) {
            $post['hvm'] = $post['hvm'];
        }
        $post['noemail'] = !isset($post['noemail']) ? 0 : $post['noemail'];
        $post['boot'] = !isset($post['boot']) ? 'dca' : $post['boot'];
        $post['band_suspend'] = !isset($post['band_suspend']) ? 0 : $post['band_suspend'];
        $post['vif_type'] = !isset($post['vif_type']) ? 'netfront' : $post['vif_type'];
        if ($edit == 0) {
            $post['addvps'] = !isset($post['addvps']) ? 1 : (int)$post['addvps'];
        } else {
            $post['editvps'] = !isset($post['editvps']) ? 1 : $post['editvps'];
            $post['acpi'] = !isset($post['acpi']) ? 1 : $post['acpi'];
            $post['apic'] = !isset($post['apic']) ? 1 : $post['apic'];
            $post['pae'] = !isset($post['pae']) ? 1 : $post['pae'];
            $post['dns'] = !isset($post['dns']) ? array('4.2.2.1', '4.2.2.2') : $post['dns'];
            $post['editvps'] = !isset($post['editvps']) ? 1 : (int)$post['editvps'];
        }

        return $post;
    }

    function cluster() {}

    function config($post = array())
    {
        $path = 'index.php?act=config';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function config_slave($post = array())
    {
        $path = 'index.php?act=config_slave';
        if (!empty($post['serid'])) {
            $path .= '&changeserid=' . $post['serid'];
        }

        $ret = $this->call($path, array(), $post);
        return $ret;
    }
    /**
     * Get CPU usage details
     *
     * @author Pulkit Gupta
     * @param
     * @return array The unserialised array is returned on success or
     * empty array is returned on failure
     */
    function cpu($serverid = 0)
    {
        $path = 'index.php?act=manageserver&changeserid=' . $serverid;
        $ret = $this->call($path);
        return $ret['usage']['cpu'];
    }

    function serverloads($post = array())
    {
        $path = 'index.php?act=serverloads';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function createssl($post)
    {
        $path = 'index.php?act=createssl';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function letsencrypt($post)
    {
        $path = 'index.php?act=letsencrypt';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function createtemplate($post)
    {
        $path = 'index.php?act=createtemplate';
        $post['createtemp'] = 1;
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function server_stats($post = array())
    {
        $path = 'index.php?act=server_stats' . (!empty($post['serid']) ? '&changeserid=' . (int)$post['serid'] : '') . (!empty($post['show']) ? '&show=' . (int)$post['show'] : '');
        $ret = $this->call($path, array(), $post);
        return $ret;
    }
    function vps_stats($post = array())
    {
        $path = 'index.php?act=vps_stats' . (!empty($post['serid']) ? '&changeserid=' . (int)$post['serid'] : '') . (!empty($post['page']) && !empty($post['reslen']) ? '&page=' . (int)$post['page'] . '&reslen=' . (int)$post['reslen'] : '');
        $ret = $this->call($path, array(), $post);

        if (!empty($ret['vps_data']) && is_array($ret['vps_data'])) {
            foreach ($ret['vps_data'] as $k => $v) {
                $tmp_k = str_replace('K_', '', $k);
                $tmp_vpsdata[$tmp_k] = $v;
            }

            $ret['vps_data'] = $tmp_vpsdata;
            unset($tmp_vpsdata);
        }
        return $ret;
    }

    function databackup($post)
    {
        $path = 'index.php?act=databackup';
        $ret = $this->call($path, array(), $post);

        return $ret;
    }

    function listdbbackfiles()
    {
        $path = 'index.php?act=databackup';
        $ret = $this->call($path);
        return $ret;
    }
    function createvpsbackup($post)
    {
        $path = 'index.php?act=editbackup_plan';
        if (!empty($post['vpsid'])) {
            $path = 'index.php?act=managevps&vpsid=' . $post['vpsid'];
            $post = array();
            $post['cbackup'] = 1;
        }
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function vps_backup_list($post)
    {
        $use_backuply = '';
        if ($post['backup_type'] == 'use_backuply') {
            $use_backuply = '&use_backuply=1;';
        }
        $path = 'index.php?act=vpsrestore&op=get_vps&vpsid=' . $post['vpsid'] . $use_backuply;
        if (!empty($post['serid'])) {
            $path .= '&changeserid=' . $post['serid'];
        }
        $res = $this->call($path, array(), $post);
        return $res;
    }

    function vpsrestore($post)
    {
        $post['restore'] = 1;
        $path = 'index.php?act=vpsrestore';
        if (!empty($post['serid'])) {
            $path .= '&changeserid=' . $post['serid'];
        }
        $ret = $this->call($path, array(), $post);

        unset($ret['restore_details']);

        return $ret;
    }

    function deletevpsbackup($post)
    {
        $path = 'index.php?act=vpsrestore';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }
    function pdns($page, $reslen, $post = array())
    {
        if (empty($post)) {
            $path = 'index.php?act=pdns&page=' . $page . '&reslen=' . $reslen;
            $ret = $this->call($path, array(), $post);
        } elseif (isset($post['test'])) {
            $path = 'index.php?act=pdns&test=' . $post['test'];
            $ret = $this->call($path);
        } elseif (isset($post['delete'])) {
            $path = 'index.php?act=pdns';
            $ret = $this->call($path, array(), $post);
        } else {
            $path = 'index.php?act=pdns&pdns_name=' . urlencode($post['pdns_name']) . '&pdns_ipaddress=' . $post['pdns_ipaddress'] . '&page=' . $page . '&reslen=' . $reslen;
            $ret = $this->call($path, array(), $post);
        }
        return $ret;
    }

    function rdns($post = array())
    {
        $path = 'index.php?act=rdns';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function domains($page = 1, $reslen = 50, $post = array())
    {
        $path = 'index.php?act=domains&pdnsid=' . $post['pdnsid'] . '&page=' . $page . '&reslen=' . $reslen;
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function delete_dnsrecords($post = array())
    {
        $path = 'index.php?act=dnsrecords&pdnsid=' . $post['pdnsid'];
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function dnsrecords($page = 1, $reslen = 50, $post = array())
    {
        if (!isset($post['del'])) {
            $path = 'index.php?act=dnsrecords&pdnsid=' . $post['pdnsid'] . '&domain_id=' . $post['domain_id'] . '&page=' . $page . '&reslen=' . $reslen;
            $ret = $this->call($path);
        } else {
            $path = 'index.php?act=dnsrecords&pdnsid=' . $post['pdnsid'] . '&domain_id=' . $post['domain_id'];
            $ret = $this->call($path, array(), $post);
        }
        return $ret;
    }
    function search_dnsrecords($page = 1, $reslen = 50, $post = array())
    {
        $path = 'index.php?act=dnsrecords&pdnsid=' . $post['pdnsid'] . '&domain_id=' . $post['domain_id'] . '&dns_name=' . $post['dns_name'] . '&dns_domain=' . $post['dns_domain'] . '&record_type=
        ' . $post['record_type'] . '&page=' . $page . '&reslen=' . $reslen;
        $ret = $this->call($path, array(), $post);

        return $ret;
    }

    function add_dnsrecord($post = array())
    {
        $post['add_dnsrecord'] = 1;
        $path = 'index.php?act=add_dnsrecord&pdnsid=' . $post['pdnsid'];
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function edit_dnsrecord($post = array())
    {
        $post['add_dnsrecord'] = 1;
        $path = 'index.php?act=add_dnsrecord&pdnsid=' . $post['pdnsid'] . '&edit=' . $post['edit'];
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function editpdns($post = array())
    {
        $post['editpdns'] = 1;
        $path = 'index.php?act=editpdns&pdnsid=' . $post['pdnsid'];
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function defaultvsconf($post)
    {
        $path = 'index.php?act=defaultvsconf';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    /**
     * Delete a VPS
     *
     * @author Pulkit Gupta
     * @param array $post An array of DATA that should be posted
     * @return boolean 1 on success OR 0 on failure
     */
    function delete_vs($vid)
    {
        $path = 'index.php?act=vs&delete=' . (int)$vid;
        $res = $this->call($path);
        return $res;
    }
    /**
     * Single Sign On
     *
     * @author Chirag
     * @param int $vid The VMs ID
     * @return String login url
     */
    function sso()
    {
        $resp = $this->call('index.php?act=sso');

        $url = 'https://' . $this->ip . ':' . $this->port . '/' . $resp['token_key'] . '/?as=' . $resp['sid'];

        if (!empty($resp['token_key']) && !empty($resp['sid'])) {
            return $url;
        } else {
            return false;
        }
    }

    /**
     * Get Disk usage details
     *
     * @author Pulkit Gupta
     * @param
     * @return array The unserialised array is returned on success or
     * empty array is returned on failure
     */
    function disk($serverid = 0)
    {
        $path = 'index.php?act=manageserver&changeserid=' . $serverid;
        $ret = $this->call($path);
        return $ret['usage']['disk'];
    }

    function webuzo($post = array())
    {
        $post['webuzo'] = 1;
        $path = 'index.php?act=webuzo';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function webuzo_scripts()
    {
        $path = 'index.php?act=webuzo';
        $ret = $this->call($path);
        return $ret;
    }
    function editemailtemp($post)
    {
        $path = 'index.php?act=editemailtemp&temp=' . $post['temp'];
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function resetemailtemp($post)
    {
        $path = 'index.php?act=editemailtemp&temp=' . $post['temp'] . '&reset=' . $post['reset'];
        $ret = $this->call($path);
        return $ret;
    }

    function billingsettings($post = array())
    {
        $post['editsettings'] = 1;
        $path = 'index.php?act=billing';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function resourcepricing($post = array())
    {
        $post['editsettings'] = 1;
        $path = 'index.php?act=resource_pricing';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function addinvoice($post = array())
    {
        $post['addinvoice'] = 1;
        $path = 'index.php?act=addinvoice';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function editinvoice($post = array())
    {
        $post['editinvoice'] = 1;
        $path = 'index.php?act=editinvoice&invoid=' . $post['invoid'];
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function listinvoice($page = 1, $reslen = 50, $post = array())
    {
        $path = 'index.php?act=invoices&page=' . $page . '&reslen=' . $reslen;
        $ret = $this->call($path, array(), $post);
        return $ret;
    }
    function deleteinvoice($post = array())
    {
        $path = 'index.php?act=invoices';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function addtransaction($post = array())
    {
        $post['addtransaction'] = 1;
        $path = 'index.php?act=addtransaction';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function edittransaction($post = array())
    {
        $post['edittransaction'] = 1;
        $path = 'index.php?act=edittransaction&trid=' . $post['trid'];
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function listtransaction($page = 1, $reslen = 50, $post = array())
    {
        $path = 'index.php?act=transactions&page=' . $page . '&reslen=' . $reslen;
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function deletetransactions($post = array())
    {
        $path = 'index.php?act=transactions';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function editippool($post)
    {
        $post['editippool'] = 1;
        $path = 'index.php?act=editippool&ippid=' . $post['ippid'];
        $res = $this->call($path, array(), $post);
        return $res;
    }

    function deleteippool($ippid)
    {
        $path = 'index.php?act=ippool';
        $ret = $this->call($path, array(), $ippid);
        return $ret;
    }
    function editips($post)
    {
        $path = 'index.php?act=editips';
        $res = $this->call($path, array(), $post);
        return $res;
    }

    function delete_ips($post)
    {
        $path = 'index.php?act=ips';
        $res = $this->call($path, array(), $post);
        return $res;
    }

    function editplan($post)
    {
        $post['editplan'] = 1;
        $path = 'index.php?act=editplan&plid=' . $post['plid'];
        $res = $this->call($path, array(), $post);
        return $res;
    }

    function editserver($post)
    {
        $post['editserver'] = 1;
        $path = 'index.php?act=editserver&serid=' . $post['serid'];
        $res = $this->call($path, array(), $post);
        return $res;
    }

    function edittemplate($post)
    {
        $path = 'index.php?act=edittemplate&osid=' . $post['osid'];
        $post['edittemplate'] = 1;
        $res = $this->call($path, array(), $post);
        return $res;
    }
    function edituser($post)
    {
        $path = 'index.php?act=edituser&uid=' . $post['uid'];
        $res = $this->call($path, array(), $post);
        return $res;
    }

    function syncostemplate($post)
    {
        $path = 'index.php?act=ostemplates';
        $res = $this->call($path, array(), $post);
        return $res;
    }

    /**
     * Create a VPS
     *
     * @author Pulkit Gupta
     * @param array $post An array of DATA that should be posted
     * @return array The unserialized array on success OR false on failure
     */
    function editvs($post, $cookies = array())
    {
        $post['editvps'] = 1;
        $path = 'index.php?act=editvs&vpsid=' . $post['vpsid'];
        //$post = $this->clean_post($post, 1);
        $ret = $this->call($path, '', $post, $cookies);
        return array(
            'title' => $ret['title'],
            'done' => $ret['done'],
            'error' => @empty($ret['error']) ? array() : $ret['error'],
            'vs_info' => $ret['vps']
        );
    }
    function managevps($post)
    {
        $post['theme_edit'] = 1;
        $post['editvps'] = 1;
        $path = 'index.php?act=managevps&vpsid=' . $post['vpsid'];
        $ret = $this->call($path, array(), $post);
        return array(
            'title' => $ret['title'],
            'done' => $ret['done'],
            'error' => @empty($ret['error']) ? array() : $ret['error'],
            'vs_info' => $ret['vps'],
            'vps_data' => $ret['vps_data']
        );
    }

    function create_single_vps_backup($vpsid)
    {
        $path = 'index.php?act=managevps&cbackup=1&vpsid=' . $vpsid;
        $ret = $this->call($path, array());
        return $ret;
    }

    function emailconfig($post)
    {
        $path = 'index.php?act=emailconfig';
        $res = $this->call($path, array(), $post);
        return $res;
    }

    function emailtemp($post = array())
    {
        $path = 'index.php?act=emailtemp';
        $res = $this->call($path, array(), $post);
        return $res;
    }

    function filemanager($post)
    {
        $path = 'index.php?act=filemanager';
        $res = $this->call($path, '', $post);
        return $res;
    }
    function firewall($post)
    {
        $path = 'index.php?act=firewall';
        $res = $this->call($path, array(), $post);
        return $res;
    }

    function giveos() {}

    function health() {}

    function hostname($post)
    {
        $path = 'index.php?act=hostname';
        $res = $this->call($path, '', $post);
        return $res;
    }

    function import($post)
    {
        $path = 'index.php?act=import&sa=' . $post['sa'] . (!empty($post['ta']) ? '&ta=' . $post['ta'] : '') . '&changeserid=' . $post['changeserid'];
        $res = $this->call($path, array(), $post);
        return $res;
    }

    function import_solusvm($post)
    {
        $valid_ta = array('nodes', 'nodegroups', 'plans', 'users', 'ips', 'os', 'vps');
        if (empty($post['ta']) || !in_array($post['ta'], $valid_ta)) {
            return false;
        }
        $post['sa'] = 'solusvm';
        $res = $this->import($post);

        $ta_map = array('ips' => 'ipblock', 'os' => 'templates');
        //show response according to "ta" specified
        foreach ($res as $k => $v) {
            if (in_array($k, array('title', 'error', 'done', 'timenow', 'time_taken'))) continue;
            if ((empty($ta_map[$post['ta']]) && !preg_match('/' . $post['ta'] . '/is', $k)) ||
                !preg_match('/' . $ta_map[$post['ta']] . '/is', $k)
            ) {
                unset($res[$k]);
            }
        }
        return $res;
    }
    function import_proxmox($post)
    {
        $valid_ta = array('nodes', 'users', 'storages', 'vps');
        if (empty($post['ta']) || !in_array($post['ta'], $valid_ta)) {
            return false;
        }
        $post['sa'] = 'proxmox';
        $res = $this->import($post);

        $ta_map = array('ips' => 'ipblock', 'os' => 'templates');
        //show response according to "ta" specified
        foreach ($res as $k => $v) {
            if (in_array($k, array('title', 'error', 'done', 'timenow', 'time_taken'))) continue;
            if ((empty($ta_map[$post['ta']]) && !preg_match('/' . $post['ta'] . '/is', $k)) ||
                !preg_match('/' . $ta_map[$post['ta']] . '/is', $k)
            ) {
                unset($res[$k]);
            }
        }
        return $res;
    }

    function import_feathur($post)
    {
        $valid_ta = array('nodes', 'users', 'ips', 'os', 'vps');
        if (empty($post['ta']) || !in_array($post['ta'], $valid_ta)) {
            return false;
        }
        $post['sa'] = 'feathur';
        $res = $this->import($post);

        $ta_map = array('ips' => 'ipblock', 'os' => 'templates');
        //show response according to "ta" specified
        foreach ($res as $k => $v) {
            if (in_array($k, array('title', 'error', 'done', 'timenow', 'time_taken'))) continue;
            if ((empty($ta_map[$post['ta']]) && !preg_match('/' . $post['ta'] . '/is', $k)) ||
                !preg_match('/' . $ta_map[$post['ta']] . '/is', $k)
            ) {
                unset($res[$k]);
            }
        }
        return $res;
    }
    function import_hypervm($post)
    {
        $valid_ta = array('nodes', 'plans', 'users', 'ips', 'os', 'vps');
        if (empty($post['ta']) || !in_array($post['ta'], $valid_ta)) {
            return false;
        }
        $post['sa'] = 'hypervm';
        $res = $this->import($post);

        $ta_map = array('ips' => 'ipblock', 'os' => 'templates');
        //show response according to "ta" specified
        foreach ($res as $k => $v) {
            if (in_array($k, array('title', 'error', 'done', 'timenow', 'time_taken'))) continue;
            if ((empty($ta_map[$post['ta']]) && !preg_match('/' . $post['ta'] . '/is', $k)) ||
                !preg_match('/' . $ta_map[$post['ta']] . '/is', $k)
            ) {
                unset($res[$k]);
            }
        }
        return $res;
    }

    function import_openvz($post)
    {
        $post['sa'] = 'openvz';
        $res = $this->import($post);
        return $res;
    }

    function import_kvm($post)
    {
        $post['sa'] = 'kvm';
        $res = $this->import($post);
        return $res;
    }

    function import_xen($post)
    {
        $post['sa'] = 'xen';
        $res = $this->import($post);
        return $res;
    }

    function import_xcp($post)
    {
        $post['sa'] = 'xcp';
        $res = $this->import($post);
        return $res;
    }

    function import_openvz7($post)
    {
        $post['sa'] = 'openvz7';
        $res = $this->import($post);
        return $res;
    }
    function ippool($page = 1, $reslen = 50, $post = array())
    {
        if (empty($post)) {
            $path = 'index.php?act=ippool&page=' . $page . '&reslen=' . $reslen;
            $res = $this->call($path);
        } else {
            if (!isset($post['servers_search']) || $post['servers_search'] == '') {
                $post['servers_search'] = -1;
            }
            $path = 'index.php?act=ippool&poolname=' . urlencode($post['poolname']) . '&poolgateway=' . $post['poolgateway'] . '&netmask=' . $post['netmask'] . '&nameserver=' . $post['namese
        rver'] . '&servers_search=' . $post['servers_search'] . '&page=' . $page . '&reslen=' . $reslen;
            $res = $this->call($path);
        }
        return $res;
    }

    /**
     * Get list of IPs
     *
     * @author Pulkit Gupta
     * @param
     * @return array The unserialised array on success.
     */
    function ips($page = 1, $reslen = 50, $post = array())
    {
        if (empty($post)) {
            $path = 'index.php?act=ips&page=' . $page . '&reslen=' . $reslen;
        } else {
            if (!isset($post['servers_search']) || $post['servers_search'] == '') {
                $post['servers_search'] = -1;
            }
            $path = 'index.php?act=ips&ipsearch=' . $post['ipsearch'] . '&ippoolsearch=' . $post['ippoolsearch'] . '&macsearch=' . $post['macsearch'] . '&vps_search=' . $post['vps_search'] . '
        &servers_search=' . $post['servers_search'] . '&lockedsearch=' . $post['lockedsearch'] . '&ippid=' . $post['ippid'] . '&page=' . $page . '&reslen=' . $reslen;
        }
        $ret = $this->call($path);
        $tmp_ippool = array();
        if (!empty($ret['ips'])) {
            foreach ($ret['ips'] as $ip) {
                if (!empty($ip['ippid'])) {
                    $tmp_ippool[$ip['ippid']] = $ret['ippools'][$ip['ippid']];
                }
            }
        }
        $ret['ippools'] = $tmp_ippool;
        return $ret;
    }
    function iso()
    {
        $path = 'index.php?act=iso';
        $ret = $this->call($path);
        return $ret;
    }

    function kernelconf($post = 0)
    {
        $path = 'index.php?act=kernelconf';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function license()
    {
        $path = 'index.php?act=license';
        $ret = $this->call($path);
        return $ret;
    }

    /**
     * List VPS
     *
     * @author Pulkit Gupta
     * @param int page number, if not specified then only 50 records are returned.
     * @return array The unserialized array on success OR false on failure
     *
     */
    function listvs($page = 1, $reslen = 50, $search = array())
    {

        if (empty($search)) {
            $path = 'index.php?act=vs&page=' . $page . '&reslen=' . $reslen;
        } else {
            $path = 'index.php?act=vs&search=1&page=' . $page . '&reslen=' . $reslen;
            foreach ($search as $k => $v) {
                $path .= '&' . $k . '=' . $v;
            }
        }

        $result = $this->call($path);
        if (!empty($result['error'])) {
            unset($result['vs']);
        }
        $ret = $result;
        return $ret;
    }
    function update_vps_net_rules($vpsid)
    {
        if (!empty($vpsid)) {
            $path = 'index.php?act=vs&action=vs_netrestrict&vpsid=' . $vpsid;
            $result = $this->call($path);
            return $result;
        }
    }

    function login() {}

    function loginlogs($page = 1, $reslen = 50, $post = array())
    {
        if (empty($post)) {
            $path = 'index.php?act=loginlogs&page=' . $page . '&reslen=' . $reslen;
            $ret = $this->call($path);
        } else {
            $path = 'index.php?act=loginlogs&username=' . $post['username'] . '&ip=' . $post['ip'] . '&page=' . $page . '&reslen=' . $reslen;
            $ret = $this->call($path, array(), $post);
        }
        return $ret;
    }

    function logs($page = 1, $reslen = 50, $post = array())
    {
        if (empty($post)) {
            $path = 'index.php?act=logs&page=' . $page . '&reslen=' . $reslen;
            $ret = $this->call($path);
        } else {
            $path = 'index.php?act=logs&id=' . $post['id'] . '&email=' . $post['email'] . '&page=' . $page . '&reslen=' . $reslen;
            $ret = $this->call($path, array(), $post);
        }
        return $ret;
    }

    function maintenance($post)
    {
        $path = 'index.php?act=maintenance';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }
    function makeslave() {}

    function os($post = array())
    {
        if (empty($post)) {
            $path = 'index.php?act=os';
        } else {
            $path = 'index.php?act=os&getos=' . $post['osids'][0];
        }
        $result = $this->call($path, array(), $post);
        return $result;
    }

    function ostemplates($page = 1, $reslen = 50)
    {
        $path = 'index.php?act=ostemplates&page=' . $page . '&reslen=' . $reslen;
        $result = $this->call($path);
        // $ret['title'] = $result['title'];
        // $ret['ostemplates'] = $result['ostemplates'];
        return $result;
    }

    function delostemplates($post = array())
    {
        $path = 'index.php?act=ostemplates&delete=' . $post['delete'];
        $result = $this->call($path);
        $ret['title'] = $result['title'];
        $ret['done'] = $result['done'];
        $ret['ostemplates'] = $result['ostemplates'];
        return $ret;
    }

    function performance($serid, $option = "")
    {

        $path = 'index.php?act=performance&changeserid=' . $serid;
        if ($option == 'network_stats') {
            $path .= '&network_stats=1';
        } elseif ($option == 'live_stats') {
            $path .= '&ajax=true';
        }

        $result = $this->call($path);
        return $result;
    }

    function phpmyadmin() {}
    function plans($page = 1, $reslen = 50, $search = array())
    {
        if (empty($search)) {
            $path = 'index.php?act=plans&page=' . $page . '&reslen=' . $reslen;
            $ret = $this->call($path);
        } else {
            $path = 'index.php?act=plans&planname=' . urlencode($search['planname']) . '&ptype=' . $search['ptype'] . '&page=' . $page . '&reslen=' . $reslen;
            $ret = $this->call($path);
        }
        return $ret;
    }

    function sort_plans($page = 1, $reslen = 50, $sort = array())
    {
        $path = 'index.php?act=plans&sortcolumn=' . $sort['sortcolumn'] . '&sortby=' . $sort['sortby'] . '&page=' . $page . '&reslen=' . $reslen;
        $ret = $this->call($path);
        return $ret;
    }

    function delete_plans($post)
    {
        $path = 'index.php?act=plans&delete=' . $post['delete'];
        $ret = $this->call($path);
        return $ret;
    }

    function list_user_plans($post = array(), $page = 1, $reslen = 50)
    {
        $path = 'index.php?act=user_plans&page=' . $page . '&reslen=' . $reslen;
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function add_user_plans($post = array())
    {
        $post['adduser_plans'] = 1;
        $path = 'index.php?act=adduser_plans';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function edit_user_plans($post)
    {
        $post['edituser_plans'] = 1;
        $path = 'index.php?act=edituser_plans&uplid=' . $post['uplid'];
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    function delete_user_plans($post = array())
    {
        $path = 'index.php?act=user_plans';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }
    /**
     * POWER OFF a Virtual Server
     *
     * @author Pulkit Gupta
     * @param int $vid The VMs ID
     * @return bool TRUE on success or FALSE on failure
     */
    function poweroff($vid)
    {
        // Make the Request
        $res = $this->call('index.php?act=vs&action=poweroff&serid=0&vpsid=' . (int)$vid);
        return $res;
    }

    function processes($post = array())
    {

        $path = 'index.php?act=processes';
        $ret = $this->call($path, array(), $post);
        return $ret;
    }

    /**
     * Get RAM details
     *
     * @author Pulkit Gupta
     * @param
     * @return array The unserialised array is returned on success or
     * empty array is returned on failure
     */
    function ram($serverid = 0)
    {
        $path = 'index.php?act=manageserver&changeserid=' . $serverid;
        $ret = $this->call($path);
        return $ret['usage']['ram'];
    }

    /**
     * Rebuild a VPS
     *
     * @author Pulkit Gupta
     * @param array $post An array of DATA that should be posted
     * @return array The unserialized array on success OR false on failure
     */
    function rebuild($post)
    {
        $post['reos'] = 1;
        $vps = $this->listvs(1, 1, array('vpsid' => $post['vpsid']));
        $post['serid'] = $vps['vs'][$post['vpsid']]['serid'];
        $path = 'index.php?act=rebuild' . (!empty($post['serid']) ? '&changeserid=' . (int)$post['serid'] : '');
        return $this->call($path, '', $post);
    }
    /**
     * RESTART a Virtual Server
     *
     * @author Pulkit Gupta
     * @param int $vid The VMs ID
     * @return bool TRUE on success or FALSE on failure
     */
    function restart($vid)
    {
        // Make the Request
        $res = $this->call('index.php?act=vs&action=restart&serid=0&vpsid=' . (int)$vid);
        return $res;
    }

    function restartservices($post)
    {
        $post['do'] = 1;
        $path = 'index.php?act=restartservices&service=' . $post['service'] . '&do=' . $post['do'];
        $res = $this->call($path, array(), $post);
        return $res;
    }

    /**
     * Current server information
     *
     * @author Pulkit Gupta
     * @param
     * @return array The unserialized array on success OR false on failure
     */
    function serverinfo($serid = 0)
    {

        $path = 'index.php?act=serverinfo';
        if (!empty($serid)) {
            $path .= '&changeserid=' . $serid;
        }
        $result = $this->call($path);

        $ret = array();
        $ret['title'] = $result['title'];
        $ret['info']['masterkey'] = $result['info']['masterkey'];
        $ret['info']['path'] = $result['info']['path'];
        $ret['info']['key'] = $result['info']['key'];
        $ret['info']['pass'] = $result['info']['pass'];
        $ret['info']['kernel'] = $result['info']['kernel'];
        $ret['info']['num_vs'] = $result['info']['num_vs'];
        $ret['info']['version'] = $result['info']['version'];
        $ret['info']['patch'] = $result['info']['patch'];

        return $ret;
    }
    /**
     * List Servers
     *
     * @author Pulkit Gupta
     * @param
     * @return array The unserialized array on success OR false on failure
     */
    function servers($search = array(), $del_serid = 0)
    {
        if ($del_serid == 0) {
            $path = 'index.php?act=servers';
            if (!empty($search)) {
                $path .= '&servername=' . urlencode($search['servername']) . '&serverip=' . $search['serverip'] . '&ptype=' . $search['ptype'] . '&search=Search';
            }
        } else {
            $path = 'index.php?act=servers&delete=' . $del_serid;
        }
        return $this->call($path);
    }

    function server_force_delete($del_serid = 0)
    {
        if ($del_serid == 0) {
            $path = 'index.php?act=servers';
        } else {
            $path = 'index.php?act=servers&force=' . $del_serid;
        }
        return $this->call($path);
    }

    function listservers()
    {
        $path = 'index.php?act=servers';
        return $this->call($path);
    }
    function services($post = array())
    {
        $path = 'index.php?act=services';
        $res = $this->call($path, array(), $post);
        return $res;
    }

    function ssh()
    {
        /* $path = 'index.php?act=ssh';
        $res = $this->call($path);
        return $res;*/
    }

    function ssl($post = 0)
    {
        $path = 'index.php?act=ssl';
        $res = $this->call($path, array(), $post);
        return $res;
    }

    function sslcert()
    {
        /* $path = 'index.php?act=sslcert';
        $res = $this->call($path);
        return $res;*/
    }

    /**
     * START a Virtual Server
     *
     * @author Pulkit Gupta
     * @param int $vid The VMs ID
     * @return bool TRUE on success or FALSE on failure
     */
    function start($vid)
    {

        $res = $this->call('index.php?act=vs&action=start&serid=0&vpsid=' . (int)$vid);
        return $res;
    }

    /**
     * STOP a Virtual Server
     *
     * @author Pulkit Gupta
     * @param int $vid The VMs ID
     * @return bool TRUE on success or FALSE on failure
     */
    function stop($vid)
    {
        // Make the Request
        $res = $this->call('index.php?act=vs&action=stop&serid=0&vpsid=' . (int)$vid);
        return $res;
    }
    /**
     * Gives status of a Virtual Server
     *
     * @author Pulkit Gupta
     * @param Array $vids array of IDs of VMs
     * @return Array Contains the status info of the VMs
     */
    function status($vids)
    {

        // Make the Request
        $res = $this->call('index.php?act=vs&vs_status=' . implode(',', $vids));
        return $res['status'];
    }

    /**
     * Suspends a VM of a Virtual Server
     *
     * @author Pulkit Gupta
     * @param int $vid The VMs ID
     * @param String $reason The reason to suspend the VM
     * @return int 1 if the VM is ON, 0 if its OFF
     */
    function suspend($vid, $post = array())
    {
        $path = 'index.php?act=vs&suspend=' . (int)$vid;
        $res = $this->call($path, array(), $post);
        return $res;
    }

    /**
     * Unsuspends a VM of a Virtual Server
     *
     * @author Pulkit Gupta
     * @param int $vid The VMs ID
     * @return int 1 if the VM is ON, 0 if its OFF
     */
    function unsuspend($vid)
    {
        $path = 'index.php?act=vs&unsuspend=' . (int)$vid;
        $res = $this->call($path);
        return $res;
    }

    function suspend_net($vid)
    {
        $path = 'index.php?act=vs&suspend_net=' . $vid;
        $res = $this->call($path);
        return $res;
    }
    function unsuspend_net($vid)
    {
        $path = 'index.php?act=vs&unsuspend_net=' . $vid;
        $res = $this->call($path);
        return $res;
    }

    function lock($vid, $reason = '')
    {
        $path = 'index.php?act=vs&action=lock&vpsid=' . $vid;
        $res = $this->call($path, array(), array('reason' => $reason));
        return $res;
    }

    function unlock($vid)
    {
        $path = 'index.php?act=vs&action=unlock&vpsid=' . $vid;
        $res = $this->call($path);
        return $res;
    }

    function tools() {}

    function ubc($post)
    {
        $path = 'index.php?act=ubc';
        $res = $this->call($path, array(), $post);
        return $res;
    }

    function updates($post)
    {
        $path = 'index.php?act=updates';
        $res = $this->call($path, array(), $post);
        return $res;
    }

    function userlogs($page = 1, $reslen = 50, $post = array())
    {
        if (empty($post)) {
            $path = 'index.php?act=userlogs&page=' . $page . '&reslen=' . $reslen;
            $res = $this->call($path);
        } else {
            $path = 'index.php?act=userlogs&vpsid=' . $post['vpsid'] . '&email=' . $post['email'] . '&page=' . $page . '&reslen=' . $reslen;
            $res = $this->call($path, array(), $post);
        }
        return $res;
    }
    function iplogs($page = 1, $reslen = 50, $post = array())
    {
        if (empty($post)) {
            $path = 'index.php?act=iplogs&page=' . $page . '&reslen=' . $reslen;
            $res = $this->call($path);
        } else {
            $path = 'index.php?act=iplogs&vpsid=' . $post['vpsid'] . '&ip=' . $post['ip'] . '&page=' . $page . '&reslen=' . $reslen;
            $res = $this->call($path, array(), $post);
        }
        return $res;
    }

    function deleteiplogs($post)
    {
        if (!empty($post)) {
            $path = 'index.php?act=iplogs';
            $res = $this->call($path, array(), $post);
        }
        return $res;
    }

    function users($page = 1, $reslen = 50, $post = array())
    {
        if (empty($post)) {
            $path = 'index.php?act=users&page=' . $page . '&reslen=' . $reslen;
            $res = $this->call($path, array(), $post);
        } else {
            $path = 'index.php?act=users&uid=' . $post['uid'] . '&email=' . $post['email'] . '&type=' . $post['type'] . '&page=' . $page . '&reslen=' . $reslen;
            $res = $this->call($path, array(), $post);
        }
        return $res;
    }

    function delete_users($del_userid)
    {
        $path = 'index.php?act=users';
        $res = $this->call($path, array(), $del_userid);
        return $res;
    }

    function vnc($post)
    {
        $path = 'index.php?act=vnc&novnc=' . $post['novnc'];
        $res = $this->call($path, array(), $post);
        return $res;
    }
    function vs($page = 1, $reslen = 50)
    {
        $path = 'index.php?act=vs&page=' . $page . '&reslen=' . $reslen;
        $res = $this->call($path);
        return $res;
    }

    function vsbandwidth()
    {
        $path = 'index.php?act=vsbandwidth';
        $res = $this->call($path);
        return $res;
    }

    function vscpu()
    {
        $path = 'index.php?act=vscpu';
        $res = $this->call($path);
        return $res;
    }

    function vsram()
    {
        $path = 'index.php?act=vsram';
        $res = $this->call($path);
        return $res;
    }

    function clonevps($post)
    {
        $path = 'index.php?act=clone';
        $post['migrate'] = 1;
        $post['migrate_but'] = 1;
        $res = $this->call($path, array(), $post);
        return $res;
    }

    function migrate($post)
    {
        $path = 'index.php?act=migrate';
        $res = $this->call($path, array(), $post);
        return $res;
    }

    function haproxy($post)
    {
        $path = 'index.php?act=haproxy';
        $res = $this->call($path, array(), $post);
        return $res;
    }
    function listhaproxy($search = array(), $page = 1, $reslen = 50)
    {

        if (empty($search)) {
            $path = 'index.php?act=haproxy&page=' . $page . '&reslen=' . $reslen;
        } else {
            $path = 'index.php?act=haproxy&s_id=' . $search['s_id'] . '&s_serid=' . (empty($search['s_serid']) ? '-1' : $search['s_serid']) . '&s_vpsid=' . $search['s_vpsid'] . '&s_protocol=' .
                (empty($search['s_protocol']) ? '-1' : $search['s_protocol']) . '&s_src_hostname=' . $search['s_src_hostname'] . '&s_src_port=' . $search['s_src_port'] . '&s_dest_ip=' . $search['s_dest_ip'] . '&s_dest_port
        =' . $search['s_dest_port'] . '&haproxysearch=' . $search['haproxysearch'];
        }

        $result = $this->call($path);
        $ret = $result['haproxydata'];
        return $ret;
    }



    function ha($sgid = 0)
    {

        $path = 'index.php?act=ha';
        if (!empty($sgid)) {
            $path .= '&get_ha_stats=' . $sgid;
        }

        $res = $this->call($path);
        return $res;
    }

    function resetbandwidth($vpsid)
    {

        $res = $this->call('index.php?act=vs&bwreset=' . (int)$vpsid);
        return $res;
    }

    function generate_keys()
    {
        $res = $this->call('index.php?act=addvs&generate_keys=1');
        return $res['new_keys'];
    }

    function list_sshkeys($uid)
    {
        if (empty($uid)) return false;
        $post['uid'] = $uid;
        $res = $this->call('index.php?act=users&list_ssh_keys=1', array(), $post);
        $result['ssh_keys'] = $res['ssh_keys'];
        if (!empty($res['error'])) {
            $result['error'] = $res['error'];
        }
        return $result;
    }
    function add_sshkeys($post)
    {
        if (empty($post['vpsid'])) return false;
        $res = $this->call('index.php?act=managevps&vpsid=' . (int)$post['vpsid'] . '&add_ssh_keys=1', array(), $post);

        $result['done'] = $res['done'];
        if (!empty($res['error'])) {
            $result['error'] = $res['error'];
        }

        return $result;
    }

    function list_volumes($search = array(), $page = 1, $reslen = 50)
    {

        if (empty($search)) {
            $path = 'index.php?act=volumes&page=' . $page . '&reslen=' . $reslen;
        } else {
            $path = 'index.php?act=volumes&search=Search&page=' . $page . '&reslen=' . $reslen;
            if (!empty($search['disk_name'])) {
                $search['disk_name'] = urlencode($search['disk_name']);
            }
            foreach ($search as $k => $v) {
                $path .= '&' . $k . '=' . $v;
            }
        }

        $result = $this->call($path);
        $ret = $result['storage_disk'];
        return $ret;
    }

    function add_volumes($post)
    {

        if (empty($post['vpsid'])) return false;
        $path = 'index.php?act=volumes';
        $res = $this->call($path, array(), $post);
        $result['done'] = $res['done'];
        if (!empty($res['error'])) {
            $result['error'] = $res['error'];
        }

        return $result;
    }
    function perform_volumes_actions($post)
    {

        if (empty($post['disk_did_action'])) return false;
        $path = 'index.php?act=volumes';
        $res = $this->call($path, array(), $post);
        $result['done'] = $res['done'];
        if (!empty($res['error'])) {
            $result['error'] = $res['error'];
        }

        return $result;
    }

    function delete_volumes($post)
    {

        $path = 'index.php?act=volumes';
        $res = $this->call($path, array(), $post);
        if (!empty($res['done'])) {
            $result['done'] = $res['done'];
        }
        if (!empty($res['error'])) {
            $result['error'] = $res['error'];
        }

        return $result;
    }

    function get_load_balancer($page = 1, $reslen = 50, $post = [])
    {
        $path = 'index.php?act=load_balancer&page=' . $page . '&reslen=' . $reslen;
        if (!empty($post['search'])) {
            $path .= '&search=1';
            if (isset($psot['uid'])) {
                $path .= '&uid=' . $post['uid'];
            }

            if (isset($psot['hostname'])) {
                $path .= '&hostname=' . $post['hostname'];
            }

            if (isset($psot['sgid'])) {
                $path .= '&sgid=' . $post['sgid'];
            }
        }

        return $this->call($path, array(), $post);
    }
    function delete_load_balancer($lb_uuid, $delete_added_vm = 0)
    {
        $path = 'index.php?act=load_balancer';
        $post['delete_lb'] = $lb_uuid;
        if (!empty($delete_added_vm)) {
            $post['delete_added_vm'] = $delete_added_vm;
        }
        return $this->call($path, array(), $post);
    }

    function add_to_load_balancer($lb_uuid, $vpsid)
    {
        $path = 'index.php?act=load_balancer';
        $post['select_lb'] = $lb_uuid;
        $post['vpsid'] = $vpsid;
        return $this->call($path, array(), $post);
    }

    function manage_load_balancer($lb_uuid, $post = [])
    {
        $path = 'index.php?act=manage_load_balancer&lb_uuid=' . $lb_uuid;
        return $this->call($path, array(), $post);
    }

    function passthrough_pool($search = [], $page = 1, $reslen = 50)
    {
        if (empty($search)) {
            $path = 'index.php?act=passthrough&page=' . $page . '&reslen=' . $reslen;
            $ret = $this->call($path);
        } else {
            $path = 'index.php?act=passthrough&pid=' . $search['pid'] . '&pname=' . $search['pname'] . '&page=' . $page . '&reslen=' . $reslen;
            $ret = $this->call($path);
        }
        return $ret;
    }
    function get_nodePCIs()
    {
        $path = 'index.php?act=addpassthrough&get_nodePCIs=1';
        $res = $this->call($path);
        return $res;
    }

    function get_nodeUSBs()
    {
        $path = 'index.php?act=addpassthrough&get_nodeUSBs=1';
        $res = $this->call($path);
        return $res;
    }

    function addpassthrough($post)
    {

        if (!isset($post['passthrough_type']) || ($post['passthrough_type'] == 0 && $post['usb_key'] < 0)) {
            return false;
        }
        /* if(!isset($post['passthrough_type']) || ($post['passthrough_type']==0 && $post['pci_key'] < 0) || ($post['passthrough_type']==1 && $post['usb_key'] < 0)){
            return false;
            } */

        $res = $this->call('index.php?act=addpassthrough', array(), $post);
        $result['done'] = $res['done'];
        if (!empty($res['error'])) {
            $result['error'] = $res['error'];
        }

        return $result;
    }

    function editpassthrough($post)
    {

        if (empty($post['pid'])) {
            return false;
        }

        $res = $this->call('index.php?act=editpassthrough&pid=' . $post['pid'], array(), $post);

        $result['done'] = $res['done'];
        if (!empty($res['error'])) {
            $result['error'] = $res['error'];
        }

        return $result;
    }
    function delpassthrough($post)
    {

        if (empty($post['delete'])) {
            return false;
        }

        $res = $this->call('index.php?act=passthrough&delete=' . $post['delete'], array(), $post);

        $result['done'] = $res['done'];
        if (!empty($res['error'])) {
            $result['error'] = $res['error'];
        }

        return $result;
    }

    function user_sessions($post = [])
    {

        $res = $this->call('index.php?act=user_sessions', [], $post);

        if (!empty($res['error']) || !empty($res['fatal_error_text'])) {
            if (array_key_exists('fatal_error_text', $res)) {
                $result['error'] = $res;
            } else {
                $result['error'] = $res['error'];
            }
        } else {
            $result = $res;
        }

        return $result;
    }
    function user_notice($post = [])
    {

        $res = $this->call('index.php?act=user_notice', [], $post);

        if (!empty($res['error']) || !empty($res['fatal_error_text'])) {
            if (array_key_exists('fatal_error_text', $res)) {
                $result['error'] = $res;
            } else {
                $result['error'] = $res['error'];
            }
        } else {
            $result = $res;
        }

        return $result;
    }

    function edit_user_notice($post = [])
    {
        $post['edit_notice'] = 1;
        $res = $this->call('index.php?act=edit_user_notice', [], $post);

        if (!empty($res['error']) || !empty($res['fatal_error_text'])) {
            if (array_key_exists('fatal_error_text', $res)) {
                $result['error'] = $res;
            } else {
                $result['error'] = $res['error'];
            }
        } else {
            $result = $res;
        }

        return $result;
    }

    function list_firewall_plans($search = [], $page = 1, $reslen = 50)
    {

        $res = [];

        if (empty($search)) {
            $res = $this->call('index.php?act=firewall_plans&page=' . $page . '&reslen=' . $reslen, array());
        } else {
            $res = $this->call('index.php?act=firewall_plans&planname=' . $search['planname'] . '&page=' . $page . '&reslen=' . $reslen, array());
        }

        return $res;
    }

    function addfirewall_plan($post = [])
    {
        $res = array();
        $post['save_firewall'] = 1;

        if (empty($post['api_firewall_rules'])) {
            $res['error'] = 'Please pass $post["api_firewall_rules"]';
        } else {
            $tmp_firewall_rules = json_encode($post['api_firewall_rules']);
            $post['api_firewall_rules'] = '';
            $post['api_firewall_rules'] = $tmp_firewall_rules;
        }

        if (empty($res['error'])) {
            $res = $this->call('index.php?act=addfirewall_plan', array(), $post);
            $result['done'] = $res['done'];
        }

        if (!empty($res['error'])) {
            $result['error'] = $res['error'];
        }

        return $result;
    }

    function editfirewall_plan($post = [])
    {

        $res = array();
        $post['save_firewall'] = 1;

        if (empty($post['api_firewall_rules'])) {
            $res['error'] = 'Please pass $post["api_firewall_rules"]';
        } else {
            $tmp_firewall_rules = json_encode($post['api_firewall_rules']);
            $post['api_firewall_rules'] = '';
            $post['api_firewall_rules'] = $tmp_firewall_rules;
        }

        if (empty($res['error'])) {
            $res = $this->call('index.php?act=editfirewall_plan', array(), $post);
            $result['done'] = $res['done'];
        }

        if (!empty($res['error'])) {
            $result['error'] = $res['error'];
        }

        return $result;
    }
    function delete_firewall_plan($post = [])
    {

        $res = $result = [];

        $post['confirm_delete'] = 1;
        $res = $this->call('index.php?act=firewall_plans', array(), $post);

        $result['done'] = $res['done'];

        if (!empty($res['error'])) {
            $result['error'] = $res['error'];
        }

        return $result;
    }

    function storage_usage($post = [])
    {

        $res = $result = [];

        $res = $this->call('index.php?act=storage_usage', array(), $post);

        if (!empty($res['error'])) {
            $result['error'] = $res['error'];
        }

        $result['disks'] = $res['disks'];

        return $result;
    }
} // Class Ends
