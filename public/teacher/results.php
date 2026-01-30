<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_any_role(['teacher','admin'],'../');

$title='Results | Teacher';
$desc='Publish student results';
$base_url='../';

$my_role=$_SESSION['user']['role'];
$my_id=(int)$_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD']==='POST') {
  verify_csrf();
  $student_email=trim($_POST['student_email'] ?? '');
  $module=trim($_POST['module'] ?? '');
  $marks=(int)($_POST['marks'] ?? 0);
  $remarks=trim($_POST['remarks'] ?? '');

  if ($student_email==='' || $module==='') {
    set_flash('danger','Student email and module are required.');
  } else {
    $u=find_user_by_email($student_email);
    if (!$u || ($u['role'] ?? '') !== 'student') set_flash('danger','Student not found.');
    else {
      $sid=(int)$u['id'];
      $marks=max(0,min(100,$marks));
      $stmt=$conn->prepare("INSERT INTO grades (student_user_id, teacher_user_id, module, marks, remarks)
                            VALUES (?,?,?,?,?)");
      $stmt->bind_param("iisis",$sid,$my_id,$module,$marks,$remarks);
      $stmt->execute();
      set_flash('success','Result saved.');
      header('Location: results.php'); exit;
    }
  }
}

if ($my_role==='admin') {
  $rows=$conn->query("SELECT g.*, su.name student_name, su.email student_email, tu.name teacher_name
                      FROM grades g
                      JOIN users su ON su.id=g.student_user_id
                      JOIN users tu ON tu.id=g.teacher_user_id
                      ORDER BY g.id DESC LIMIT 120")->fetch_all(MYSQLI_ASSOC);
} else {
  $stmt=$conn->prepare("SELECT g.*, su.name student_name, su.email student_email
                        FROM grades g JOIN users su ON su.id=g.student_user_id
                        WHERE g.teacher_user_id=? ORDER BY g.id DESC LIMIT 120");
  $stmt->bind_param("i",$my_id); $stmt->execute();
  $rows=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

include __DIR__ . '/../../includes/header.php';
?>
<section class="hero">
  <h1>Results</h1>
  <p>Add grades for students (module-wise). Students can view these in their dashboard.</p>
</section>

<section class="card pad">
  <h2>Add Result</h2>
  <form method="post">
    <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
    <div class="grid">
      <div style="grid-column:span 6" class="form-row">
        <label>Student email</label>
        <input name="student_email" id="student_email" list="student_list" required placeholder="Type email...">
        <datalist id="student_list"></datalist>
      </div>
      <div style="grid-column:span 6" class="form-row">
        <label>Module</label>
        <input name="module" required placeholder="e.g., Cybersecurity Fundamentals">
      </div>
      <div style="grid-column:span 4" class="form-row">
        <label>Marks (0-100)</label>
        <input name="marks" type="number" min="0" max="100" required>
      </div>
      <div style="grid-column:span 8" class="form-row">
        <label>Remarks (optional)</label>
        <input name="remarks" placeholder="Good work...">
      </div>
      <div style="grid-column:span 12">
        <button class="btn btn-primary" type="submit">Save</button>
      </div>
    </div>
  </form>
</section>

<section class="card pad" style="margin-top:1rem">
  <h2>Recent Results</h2>
  <div style="overflow:auto">
    <table class="table">
      <thead><tr><th>ID</th><th>Student</th><th>Email</th><th>Module</th><th>Marks</th><th>Remarks</th><th>Teacher</th><th>Date</th></tr></thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><?php echo e($r['id']); ?></td>
            <td><?php echo e($r['student_name']); ?></td>
            <td><?php echo e($r['student_email']); ?></td>
            <td><?php echo e($r['module']); ?></td>
            <td><?php echo e($r['marks']); ?></td>
            <td><?php echo e($r['remarks']); ?></td>
            <td><?php echo e($r['teacher_name'] ?? 'You'); ?></td>
            <td><?php echo e($r['created_at']); ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$rows): ?><tr><td colspan="8">No results yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<script>
(() => {
  const input=document.getElementById('student_email');
  const list=document.getElementById('student_list');
  if(!input||!list) return;
  let t=null;
  input.addEventListener('input',()=>{
    const q=input.value.trim();
    clearTimeout(t);
    t=setTimeout(async ()=>{
      if(q.length<2){list.innerHTML='';return;}
      const res=await fetch('../ajax/live_search.php?q='+encodeURIComponent(q));
      const data=await res.json();
      list.innerHTML=(data||[]).map(x=>'<option value="'+x.email+'">'+x.name+'</option>').join('');
    },250);
  });
})();
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
