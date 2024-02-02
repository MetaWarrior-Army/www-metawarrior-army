<ul class="list-group">

  <li class="list-group-item list-group-item-dark d-flex justify-content-between align-items-center">
    <!-- <p class="lead">Securely stored email with 1GB of storage.</p> -->
    <?php
      if($_SESSION['userinfo']->email_active){
        echo '<a href="https://mail.metawarrior.army" class="text-dark mt-3" target="_blank"><p class="lead">Email</p>';
      }
      else{
        echo '<a href="#" class="link-secondary mt-3"><p class="lead">Email</p>';
      }
    ?>
    
    </a>
    <span class="badge bg-dark rounded-pill"><img src="/media/img/serviceicons/email_icon2.png"></span>
  </li>

  <li class="list-group-item list-group-item-dark d-flex justify-content-between align-items-center">
    <?php
      if($_SESSION['userinfo']->email_active){
        echo '<a href="https://mastodon.metawarrior.army" class="text-dark mt-3" target="_blank"><p class="lead">Social</p>';
      }
      else{
        echo '<a href="#" class="link-secondary mt-3"><p class="lead">Social</p>';
      }
    ?>
    </a>
    <span class="badge bg-dark rounded-pill"><img src="/media/img/serviceicons/mastodon_icon1.png"></span>
  </li>

  <li class="list-group-item list-group-item-dark d-flex justify-content-between align-items-center">
    <?php
      if($_SESSION['userinfo']->email_active){
        echo '<a href="https://matrix.metawarrior.army" class="text-dark mt-3" target="_blank"><p class="lead">Chat</p>';
      }
      else{
        echo '<a href="#" class="link-secondary mt-3"><p class="lead">Chat</p>';
      }
    ?>
    </a>
    <span class="badge bg-dark rounded-pill"><img src="/media/img/serviceicons/element_icon1.png"></span>
  </li>

  <li class="list-group-item list-group-item-dark d-flex justify-content-between align-items-center">
    <?php
      if($_SESSION['userinfo']->email_active){
        echo '<a href="https://discourse.metawarrior.army" class="text-dark mt-3" target="_blank"><p class="lead">Discourse</p>';
      }
      else{
        echo '<a href="#" class="link-secondary mt-3"><p class="lead">Discourse</p>';
      }
    ?>
    </a>
    <span class="badge bg-dark rounded-pill"><img src="/media/img/serviceicons/discourse_icon1.png"></span>
  </li>

  <li class="list-group-item list-group-item-dark d-flex justify-content-between align-items-center">
    <p class="lead text-secondary">DAO</p>
    <span class="badge bg-dark rounded-pill"><p class="lead">👹</p></span>
  </li>

  <li class="list-group-item list-group-item-dark d-flex justify-content-between align-items-center">
    <p class="text-secondary lead">Subs</p>
    <span class="badge bg-dark rounded-pill"><img src="/media/img/serviceicons/listmonk_icon0.png"></span>
  </li>

  <li class="list-group-item list-group-item-dark d-flex justify-content-between align-items-center">
    <p class="text-secondary lead">Support</p>
    <span class="badge bg-dark rounded-pill"><img src="/media/img/serviceicons/support_icon0.png"></span>
  </li>


</ul>


