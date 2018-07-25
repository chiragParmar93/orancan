<?php
class Users_model extends CI_Model{
    public $table = "users";
    public function get_users($params = array()){
        $this->db->select('*');
        $this->db->from('users');
        $this->db->order_by('id','desc');
        
        if(array_key_exists("start",$params) && array_key_exists("limit",$params)){
            $this->db->limit($params['limit'],$params['start']);
        }elseif(!array_key_exists("start",$params) && array_key_exists("limit",$params)){
            $this->db->limit($params['limit']);
        }
        
        $query = $this->db->get();
        
        return ($query->num_rows() > 0)?$query->result():FALSE;
    }
    public function get_datatables($searchArray, $flag = 'listing') {
        //pr($searchArray, 1);
        extract($searchArray);
        $this->db->select("a.*");
        $this->db->from($this->table . ' AS a');
        if ($sort != "") {
            if ($sort == '0') {
                $sort = "a.id";
                $order = "desc";
            } else {
                $sort = "a.first_name";
            }
            $this->db->order_by($sort, $order);
        }else {
            $this->db->order_by('a.id', "DESC");
        }
        //$this->db->group_by('a.NotesId');
        if ($flag == 'count') {
            $query = $this->db->get();
            return $query->num_rows();
        } else {
            if ($rows != '') {
                $this->db->limit($rows, $page);
            }
            $query = $this->db->get();
            //pr($this->db->last_query(), 1);
            return $query->result_array();
        }
    }

    public function insert_product()
    {    
        $data = array(
            'title' => $this->input->post('title'),
            'description' => $this->input->post('description')
        );
        return $this->db->insert('products', $data);
    }
    public function update_product($id) 
    {
        $data=array(
            'title' => $this->input->post('title'),
            'description'=> $this->input->post('description')
        );
        if($id==0){
            return $this->db->insert('products',$data);
        }else{
            $this->db->where('id',$id);
            return $this->db->update('products',$data);
        }        
    }
}
?>