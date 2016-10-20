<?php

namespace Bartleby\Pages;

require_once __DIR__.'/BasePage.php';
require_once BARTLEBY_ROOT_FOLDER .'Commons/EndPoints/Auth.php';


use Bartleby\Core\CallDataRawWrapper;
use Bartleby\Pages\BasePage;

final class SignInCallData extends CallDataRawWrapper {

    const userUID="userUID";

    const password='password';

}

final class SignIn extends BasePage {


    function GET() {
        return $this->getDocument();
    }

    function POST() {
        return $this->getDocument();
    }


    function  setup() {
        parent::setup();
        $this->addCSS($this->absoluteUrl('/static/css/fixed.css'));
        $this->title='SignIn';

    }


    function mainContent() {
        $spaceUID=$this->getSpaceUID(true);
        if(isset($spaceUID) && $this->isAuthenticated($spaceUID) ) {
            return '<script type="text/javascript">location.href = "'.$this->getSignOutPageURL().'";</script>';
        }else{
            return '
    <div class="site-wrapper">
      <div class="site-wrapper-inner">
        <div class="cover-container">
         ' . $this->getNavigation("/signIn") . '
          <div class="inner cover">
              <div class="col-md-2 col-md-offset-5">
                <form  id="authForm" method="post">
                <h2 class="form-signin-heading">Please sign in</h2>
                    <label for="spaceUID" class="sr-only">Space UID</label>
                    <input type="text" id="spaceUID" class="form-control" placeholder="DataSpace UID" required autofocus '.$this->valueForSpaceUID().'>
                    <label for="inputUID" class="sr-only">User UID</label>
                    <input type="text" id="inputUID" class="form-control" placeholder="User UID" required '.$this->valueForUserUID().'>
                    <label for="inputPassword" class="sr-only">Password</label>
                    <input type="password" id="password-input" name="password" class="form-control" required ' . $this->_valueForPassword() . '>
                    <br>
                    <button id="sbutton" class="btn btn-lg btn-default btn-block" type="submit">Sign in</button>
                 </form>
                 <script>
                    $(function(){
                        $("#authForm").on("submit", function(e){
                            e.preventDefault();
                            $.ajax({
                                url: "' . $this->getSignInApiURL() . '",
                                type: "POST",
                                data: $("#authForm").serialize(),
                                headers: ' . $this->_loginHTTPHeaders() . ',
                                success: function(data){
                                   // alert(data);
                                    window.location.href ="'.$this->getSignOutPageURL().'";
                                }
                            });
                        });
                    });
                </script>
              </div>
          </div>
        </div>
      </div>
    </div>' . $this->footer();
        }

    }


    function valueForSpaceUID(){
        $spaceUID=$this->getModel()->getValueForKey(SPACE_UID_KEY);
        if (isset($spaceUID)){
            return 'value="'.$spaceUID.'" ';
        }else{
            return '';
        }
    }

    function  valueForUserUID(){
        $userUID=$this->getModel()->getValueForKey(SignInCallData::userUID);
        if (isset($userUID)){
            return 'value="'.$userUID.'" ';
        }else{
            return '';
        }
    }

    private function  _valueForPassword(){
        $password=$this->getModel()->getValueForKey(SignInCallData::password);
        $password=base64_decode($password);
        if (isset($password)){
            return 'value="'.$password.'" ';
        }else{
            return '';
        }
    }

    function getSignInApiURL(){
        $url=$this->getConfiguration()->BASE_URL();
        $url=$this->getApiBaseURL().'user/login?identification=cookie&spaceUID='.$this->getSpaceUID(true).'&userUID='.$this->getModel()->getValueForKey(SignInCallData::userUID);
        return $url;
    }
    

    private function  _loginHTTPHeaders(){
        $spaceUID=$this->getSpaceUID(true);
        if (isset($spaceUID) && $spaceUID!=""){
            return json_encode($this->getConfiguration()->httpHeadersWithToken($this->getSpaceUID(false),"LoginUser"));
        }else{
            // Void headers
            return json_encode([]);
        }
    }
}