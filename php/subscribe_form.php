<form action="/sitrep" method="POST">
  <div class="mb-3">
    <label for="exampleInputEmail1" class="form-label">Email address</label>
    <input type="email" class="form-control" id="email1" name="email" aria-describedby="emailHelp" onChange="checkForm();">
    <div id="emailHelp" class="form-text text-warning">We'll never share your email with anyone else.</div>
  </div>
  <div class="mb-3 form-check">
    <input type="checkbox" class="form-check-input" id="check1" name="check" onChange="checkForm();">
    <label class="form-check-label small" for="exampleCheck1">I am the owner of this email address.</label>
  </div>
  <button type="submit" id="submit" class="btn btn-outline-info mb-3" disabled>Submit</button>
</form>