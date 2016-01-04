<?php
/**
 * 这个类是对 当前登录用户Model的一个wrapper 
 */
class Angel_Me{
    
    private $user = null;
    
    /**
     *
     * @param \Documents\User $user 
     */
    public function __construct(\Documents\User $user){
        $this->user = $user;
    }
    
    /**
     * 
     * @return \Documents\User
     */
    public function getUser(){
        return $this->user;
    }
    
    public function setUser(\Documents\User $user){
        $this->user = $user;
    }
    
    /**
     * 获得me的id 
     */
    public function getId(){
        return $this->user->id;
    }
    
    /**
     * 帐号是否被激活，Email地址是否验证了
     * @return type 
     */
    public function isActivated(){
        return $this->user->isEmailValidated() === true ? true : false;
    }
    
    /**
     * 用户是否通过审核
     * @return boolean 
     */
    public function isValidated(){
        return $this->user->isValidated() === true ? true : false;
    }
    
    /**
     * 用户信息是否正在审核中
     * @param model $user 
     */
    public function isInValidatedList(){
        return $this->user->isInValidatedList() === true ? true : false;
    }
    
    /**
     * 是否为管理员 
     */
    public function isAdmin(){
        return $this->user->isAdmin() === true ? true : false;
    }
    
    
    public function isStartup(){
        return $this->user->isStartup();
    }
    
    public function isInvestor(){
        return $this->user->isInvestor();
    }
    
    public function getProfileImage(){
        return $this->user->profile_image;
    }
    
    public function getUserType() {
        return $this->user->user_type;
    }
}
