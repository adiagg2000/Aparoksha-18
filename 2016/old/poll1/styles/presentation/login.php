<?php

class Login
{
	public $mLinkToLogin = '';
	public $mLinkToLogout = '';
	public $mUsername = '';
	public $isLogin = 0;
	public $hasVoted = 0;
	public $myvote_url = '';
	public $mAuthenticate = 0;
	public $showMyVote = 0;
	public $index_url = '';
	public $total_votes;
	public $poll_complete = 0;
	
	private $mPassword;
	
	public function __construct()
	{
		$this -> mLinkToLogin = Link::Build(str_replace(VIRTUAL_LOCATION, '', getenv('REQUEST_URI')));
		$this -> mLinkToLogout = Link::Build('index.php?Page=Logout');
		$this -> index_url = Link::toIndex();
		$this -> total_votes = Content::getTotalVotes();
		$this -> placeorder_url = Link::PlaceOrder();
		if(POLL_COMPLETE)
		{
			$this -> poll_complete = 1;
		}
		
		if(isset($_SESSION['HasVoted']) && $_SESSION['HasVoted'] == true)
		{
			$this -> hasVoted = 1;
			$this -> myvote_url = Link::VoteUrl();
		}
			
		
		if(isset($_GET['Page']) && $_GET['Page'] == 'Logout')
		{
			session_unset();
			header('Location: ' . Link::toIndex());
		}
		
		if(isset($_SESSION['LoginStatus']) && $_SESSION['LoginStatus'] == true )
		{
			$this -> isLogin = 1;
			$this -> mUsername = $_SESSION['UserId'];
			
		}
		
		if(isset($_SESSION['UserId'])  && isset($_SESSION['LoginStatus']) )
		{
			if(Content::hasVotedOne($_SESSION['UserId']))
			{
				$this -> showMyVote = 1;
				$this -> myvote_url = Link::VoteUrl();			
			}
		}
	}
	
	public static function chk_user( $uid, $pwd ) 
	{
		if ($pwd) 
		{
			$ds = ldap_connect("172.31.1.42");
			ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
			$a = ldap_search($ds, "dc=iiita,dc=ac,dc=in", "uid=$uid" );
			$b = ldap_get_entries( $ds, $a );
			if(count($b) > 1)
				$dn = $b[0]["dn"];
			else 
				return 0;
			
			$ldapbind=@ldap_bind($ds, $dn, $pwd);
			
			if ($ldapbind) 
				return 1;
			else 
				return 0;
			
			ldap_close($ds);
		}
		else 
			return 0;
	}
	
	public function init()
	{
		
		if(!isset($_SESSION['LoginStatus']) || $_SESSION['LoginStatus'] == false)
		{
			if(isset($_POST['login']))
			{
				$this -> mUsername = strtolower($_POST['username']);
				$this -> mPassword = $_POST['password'];
				
				if( self::chk_user($this -> mUsername, $this -> mPassword) ) // check for authentication 
				{
					$this -> mAuthenticate = 1;
					$_SESSION['LoginStatus'] = true;
					$_SESSION['UserId'] = $this -> mUsername;
					
					if(Content::hasVoted($this -> mUsername))
					{
						$_SESSION['HasVoted'] = true;
						$this -> hasVoted = 1;
					}
					else
						$_SESSION['HasVoted'] = false;
					
					if(!Content::CheckUser($this -> mUsername))
						Content::AddUser($this -> mUsername);
					
					header('Location: ' . Link::toIndex());
				}
				else
					$this -> mAuthenticate = 2;
			}
		}	
	}				
};

?>