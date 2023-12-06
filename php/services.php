<div class="container px-4 py-5" id="hanging-icons">
              <h3 class="pb-2 border-bottom">Member Services</h3>
              <div class="row g-4 py-5 row-cols-1 row-cols-lg-3">
                <div class="col d-flex align-items-start">
                  <div class="icon-square d-inline-flex align-items-center justify-content-center fs-4 flex-shrink-0 me-3">
                    <img src="/media/img/serviceicons/email_icon2.png">
                  </div>
                  <div>
                    <h5 class="text-secondary">Email</h5>
                    <!-- <p class="lead">Securely stored email with 1GB of storage.</p> -->
                    <?php
                      if($_SESSION['userinfo']->email_active){
                        echo '<a href="https://mail.metawarrior.army" class="btn btn-outline-info mt-3">';
                      }
                      else{
                        echo '<a href="#" class="btn disabled btn-outline-secondary mt-3">';
                      }
                    ?>
                    Login
                    </a>
                  </div>
                </div>
                <div class="col d-flex align-items-start">
                  <div class="icon-square d-inline-flex align-items-center justify-content-center fs-4 flex-shrink-0 me-3">
                    <img src="/media/img/serviceicons/mastodon_icon1.png">
                  </div>
                  <div>
                    <h5 class="text-secondary">Social</h5>
                    <!-- <p class="small">Mastodon federated social media & news.</p> -->
                    <p class="small">Coming soon!</p>
                    <a href="#" class="btn disabled btn-outline-secondary mt-3">
                      Login
                    </a>
                  </div>
                </div>
                <div class="col d-flex align-items-start">
                  <div class="icon-square d-inline-flex align-items-center justify-content-center fs-4 flex-shrink-0 me-3">
                    <img src="/media/img/serviceicons/element_icon1.png">
                  </div>
                  <div>
                    <h5 class="text-secondary">Chat</h5>
                    <!-- <p class="small">Matrix secure, federated chat with voice & video calls.</p> -->
                    <p class="small">Coming soon!</p>
                    <a href="#" class="btn disabled btn-outline-secondary mt-3">
                      Login
                    </a>
                  </div>
                </div>
                <div class="col d-flex align-items-start">
                  <div class="icon-square d-inline-flex align-items-center justify-content-center fs-4 flex-shrink-0 me-3">
                    <img src="/media/img/serviceicons/discourse_icon1.png">
                  </div>
                  <div>
                    <h5 class="text-secondary">Discourse</h5>
                    <!-- <p class="small">A discussion board for proposals, support, and other thoughts from fellow members.</p> -->
                    <p class="small">Coming soon!</p>
                    <?php
                      if($_SESSION['userinfo']->email_active){
                        echo '<a href="https://discourse.metawarrior.army" class="btn btn-outline-info mt-3">';
                      }
                      else{
                        echo '<a href="#" class="btn disabled btn-outline-secondary mt-3">';
                      }
                    ?>
                      Login
                    </a>
                  </div>
                </div>
                <div class="col d-flex align-items-start">
                  <div class="icon-square d-inline-flex align-items-center justify-content-center fs-4 flex-shrink-0 me-3">
                    <p class="lead">👹</a>
                  </div>
                  <div>
                    <h5 class="text-secondary">DAO</h5>
                    <!-- <p class="small">Submit proposals to improve MWA or vote on proposals that have been approved.</p> -->
                    <p class="small">Coming soon!</p>
                    <a href="#" class="btn disabled btn-outline-secondary mt-3">
                      Vote
                    </a>
                  </div>
                </div>
                <div class="col d-flex align-items-start">
                  <div class="icon-square d-inline-flex align-items-center justify-content-center fs-4 flex-shrink-0 me-3">
                    <img src="/media/img/serviceicons/listmonk_icon0.png">
                  </div>
                  <div>
                    <h5 class="text-secondary">Subs</h5>
                    <!-- <p class="small">Get help in our forums dedicated to support. If you're unable to login and post there, feel free shooting us an email at <a href="mailto:admin@metawarrior.army" class="link-light">admin@metawarrior.army</a>.</p> -->
                    <p class="small">Coming soon!</p>
                    <a href="#" class="btn disabled btn-outline-secondary mt-3">
                      Get Help
                    </a>
                  </div>
                </div>
                <div class="col d-flex align-items-start">
                  <div class="icon-square d-inline-flex align-items-center justify-content-center fs-4 flex-shrink-0 me-3">
                    <img src="/media/img/serviceicons/support_icon0.png">
                  </div>
                  <div>
                    <h5 class="text-secondary">Support</h5>
                    <!-- <p class="small">Get help in our forums dedicated to support. If you're unable to login and post there, feel free shooting us an email at <a href="mailto:admin@metawarrior.army" class="link-light">admin@metawarrior.army</a>.</p> -->
                    <p class="small">Coming soon!</p>
                    <a href="#" class="btn disabled btn-outline-secondary mt-3">
                      Get Help
                    </a>
                  </div>
                </div>


              </div>
            </div>
