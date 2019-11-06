<head>
  <style>
    body{
      text-align: center;
    }
    ul{
      list-style: none;
    }
    .links>a {
      display: block;
      margin-bottom: 10px;
      font-size: 24px;

    }
  </style>
</head>
<body>
<h1>CUSTOM SESSION</h1>

<div class="links">
  <a href="/destroy">Destroy</a>
  <a href="/unset">Unset[name]</a>
  <a href="/gc">Garbage Collector</a>
</div>
<form action="/form" method="post">
  <input id ='changeable'type="text" name="name">
  <input id='submit' type="submit" value="Submit [name] ">
</form>

<button onclick="change()">Change name</button>


<div>
  <h2>SESSION VARIABLES</h2>
  <ul>
    <?php foreach($_SESSION as $key=>$val): ?>
    <li><?="$key = $val"?></li>
    <?php endforeach; ?>
  </ul>

<hr>

<p id="first"></p>

<p id="second"></p>

<p>session_id = <?=session_id()?></p>

<script>
document.addEventListener('DOMContentLoaded', function (){
  setTimeout(()=>{
    var req = new XMLHttpRequest();

    req.open('get', '/ajax',true);
    req.send();

    req.onload = function (){
      document.getElementById('first').innerText = this.responseText;
    }
  },100);

  setTimeout(()=>{
    var req = new XMLHttpRequest();

    req.open('get', '/ajax',true);
    req.send();

    req.onload = function (){
      document.getElementById('second').innerText = this.responseText;
    }
  },100);
})

function change(){
  var  temp = prompt() || 'name';

  document.getElementById('changeable').name=temp;
  document.getElementById('submit').value = 'Submit ['+temp+'] ';
}
</script>
</body>