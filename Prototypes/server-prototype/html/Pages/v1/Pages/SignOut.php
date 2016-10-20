<?php

namespace Bartleby\Pages;

require_once __DIR__.'/BasePage.php';
require_once BARTLEBY_ROOT_FOLDER .'Commons/EndPoints/Auth.php';

use Bartleby\Core\CallDataRawWrapper;
use Bartleby\Pages\BasePage;

final class SignOutCallData extends CallDataRawWrapper {
}

final class SignOut extends BasePage {

    function GET() {
        return $this->getDocument();
    }

    function POST() {
        return $this->getDocument();
    }


    function  setup() {
        parent::setup();
        $this->addCSS($this->absoluteUrl('/static/css/fixed.css'));
        $this->title='SignOut';
    }


    function getSignOutApiURL(){
        $url=$this->getApiBaseURL().'user/logout?identification=Cookie&spaceUID='.$this->getSpaceUID(true);
        return $url;
    }


    function mainContent() {
    return '
    <div class="site-wrapper">
      <div class="site-wrapper-inner">
        <div class="cover-container">
         ' . $this->getNavigation("/signOut") . '
          <div class="inner cover">
              <div class="col-md-2 col-md-offset-5">
                <form class="sform" method="post">
                    <button id="sbutton" class="btn btn-lg btn-default btn-block" type="submit" > Sign out</button>
                 </form>
                 <script>
                    $(function(){
                        $(".sform").on("submit", function(e){
                            e.preventDefault();
                            $.ajax({
                                url: "' . $this->getSignOutApiURL() . '",
                                type: "POST",
                                data: $(".sform").serialize(),
                                headers: ' . $this->_logoutHttpHeaders() . ',
                                success: function(data){
                                    window.location.href ="'.$this->getSignInPageURL().'";
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


    private function  _valueForPassword(){
        $password=$this->getModel()->getValueForKey(SignInCallData::password);
        if (isset($password)){
            return 'value="'.$password.'" ';
        }else{
            return '';
        }
    }


    private function _logoutHttpHeaders(){
        return json_encode($this->getConfiguration()->httpHeadersWithToken($this->getSpaceUID(),"LogoutUser"));
    }
}