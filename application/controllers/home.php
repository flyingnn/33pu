<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('M_item');
		$this->load->library('pagination');
		$this->load->model('M_cat');
	}

        /**
        * 首页控制器
        *
        */
        public function index(){
                $this->page();
        }

        /**
        * 翻页控制器
        *
        * @param integer $page 第几页
        */
	public function page($page = 1)
	{
		$this->config->load('site_info');
                //$this->output->cache(10);

		$limit=40;
		//每页显示数目

		$config['base_url'] = site_url('/home/page');
		//site_url可以防止换域名代码错误。

		$config['total_rows'] = $this->M_item->count_items();
		//这是模型里面的方法，获得总数。
                $config['use_page_numbers'] = TRUE;
                $config['first_url'] = site_url('/home');
		$config['per_page'] = $limit;
		$config['first_link'] = '首页';
		$config['last_link'] = '尾页';
		$config['num_links']=10;
		//上面是自定义文字以及左右的连接数

		$this->pagination->initialize($config);
		//初始化配置

		$data['limit']=$limit;
		$data['offset']=($page-1)*$limit;
		$data['pagination']=$this->pagination->create_links();
		//通过数组传递参数
		//以上是重点


		$query = $this->M_cat->get_all_cat();
		$data['cat'] = $query;

		//站点信息
		$data['site_name'] = $this->config->item('site_name');

		$this->load->view('home',$data);
	}

	/**
	 * 跳转函数，同时记录点击数量
	 *
	 * 点击记数要排除机器访问
	 */
	function redirect($item_id){

                $this->load->library('user_agent');
                if(!$this->agent->is_robot()){
                    $this->M_item->add_click_count($item_id);
                }

                Header("HTTP/1.1 303 See Other");
                Header("Location: ".$this->M_item->get_item_clickurl($item_id));
                exit;
	}

	/**
	 * 按URL查询商品有无做推广
	 *
	 * 
	 */
	function search(){
                $url = $this->input->get("url");
                $iid = $this->get_iid($url);
                if ($this->input->get("user_id"))
                        $outer_id = $this->input->get("user_id");
                else    $outer_id = '';
                if ($iid)
                {
                        $this->load->model('M_taobaoapi');
                        $resp = $this->M_taobaoapi->getItemDetail($iid, $outer_id);
                        if ($resp->taobaoke_item_details->taobaoke_item_detail->click_url)
                                echo $resp->taobaoke_item_details->taobaoke_item_detail->click_url;
                        else
                                echo "False";

                }
                else
                        echo "False";
                
	}
        
        /**
	 * 按URL查询商品ID
	 *
	 * 
	 */
        function get_iid($url){
                if (preg_match("/[\?&]+id=(\d+)/i",$url, $matches))
                {
                        $iid = $matches[1];
                        return $iid;
                }
                else
                    return false;    
        }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */