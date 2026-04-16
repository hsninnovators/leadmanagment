<?php
$stages = ['New Lead','Contacted','Replied','Interested','Meeting / Discussion','Proposal Sent','Follow-up','Closed Won','Not Interested','Closed Lost'];
$sources = ['Google Maps','Facebook','Instagram','LinkedIn','WhatsApp','Email','Website','Referral','Other'];
$services = ['Social Media Management','Social Media Campaigns','Static Website','Frontend Website','Portfolio Website','Company Profile / Portfolio','Online Frontend Classes','Student Project','Other'];
$priorities = ['Low','Medium','High'];
$leadTypes = ['Business','Student'];
$method = ['Phone','WhatsApp','Email','LinkedIn','Facebook','Instagram','Other'];
?>
<div class="row g-3">
<div class="col-md-3"><label>Lead Type</label><select name="lead_type" class="form-select"><?php foreach($leadTypes as $x): ?><option <?= ($lead['lead_type']??'Business')===$x?'selected':'' ?>><?= e($x) ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label>Full Name</label><input class="form-control" name="full_name" required value="<?= e($lead['full_name']??'') ?>"></div>
<div class="col-md-3"><label>Company</label><input class="form-control" name="company_name" value="<?= e($lead['company_name']??'') ?>"></div>
<div class="col-md-3"><label>Category</label><input class="form-control" name="category" value="<?= e($lead['category']??'') ?>"></div>
<div class="col-md-3"><label>City</label><input class="form-control" name="city" value="<?= e($lead['city']??'') ?>"></div>
<div class="col-md-3"><label>Country</label><input class="form-control" name="country" value="<?= e($lead['country']??'') ?>"></div>
<div class="col-md-3"><label>Phone</label><input class="form-control" name="phone" value="<?= e($lead['phone']??'') ?>"></div>
<div class="col-md-3"><label>WhatsApp</label><input class="form-control" name="whatsapp" value="<?= e($lead['whatsapp']??'') ?>"></div>
<div class="col-md-3"><label>Email</label><input type="email" class="form-control" name="email" value="<?= e($lead['email']??'') ?>"></div>
<div class="col-md-3"><label>Website</label><input class="form-control" name="website" value="<?= e($lead['website']??'') ?>"></div>
<div class="col-md-3"><label>Facebook</label><input class="form-control" name="facebook_link" value="<?= e($lead['facebook_link']??'') ?>"></div>
<div class="col-md-3"><label>Instagram</label><input class="form-control" name="instagram_link" value="<?= e($lead['instagram_link']??'') ?>"></div>
<div class="col-md-3"><label>LinkedIn</label><input class="form-control" name="linkedin_link" value="<?= e($lead['linkedin_link']??'') ?>"></div>
<div class="col-md-3"><label>Google Maps</label><input class="form-control" name="google_maps_link" value="<?= e($lead['google_maps_link']??'') ?>"></div>
<div class="col-md-3"><label>Source</label><select name="source" class="form-select"><?php foreach($sources as $x): ?><option <?= ($lead['source']??'Other')===$x?'selected':'' ?>><?= e($x) ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label>Service</label><select name="service_interest" class="form-select"><?php foreach($services as $x): ?><option <?= ($lead['service_interest']??'Social Media Management')===$x?'selected':'' ?>><?= e($x) ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><label>Budget</label><input class="form-control" name="budget_range" value="<?= e($lead['budget_range']??'') ?>"></div>
<div class="col-md-2"><label>Priority</label><select class="form-select" name="priority"><?php foreach($priorities as $x): ?><option <?= ($lead['priority']??'Medium')===$x?'selected':'' ?>><?= e($x) ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label>Stage</label><select class="form-select" name="stage"><?php foreach($stages as $x): ?><option <?= ($lead['stage']??'New Lead')===$x?'selected':'' ?>><?= e($x) ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label>Assigned Staff</label><select class="form-select" name="assigned_user_id"><?php foreach($users as $u): ?><option value="<?= (int)$u['id'] ?>" <?= (int)($lead['assigned_user_id']??0)===(int)$u['id']?'selected':'' ?>><?= e($u['full_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label>Preferred Contact</label><select class="form-select" name="preferred_contact_method"><?php foreach($method as $x): ?><option <?= ($lead['preferred_contact_method']??'Phone')===$x?'selected':'' ?>><?= e($x) ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label>Next Follow-up</label><input type="date" class="form-control" name="next_follow_up_date" value="<?= e($lead['next_follow_up_date']??'') ?>"></div>
<div class="col-md-6"><label>Issue / Pain Point</label><textarea class="form-control" name="pain_point"><?= e($lead['pain_point']??'') ?></textarea></div>
<div class="col-md-6"><label>Owner Remarks</label><textarea class="form-control" name="owner_remarks"><?= e($lead['owner_remarks']??'') ?></textarea></div>
<div class="col-md-4"><label>Tags (comma separated)</label><input class="form-control" name="tags" value="<?= e($tagsCsv ?? '') ?>"></div>
<div class="col-md-4"><label>Approval Needed (Student Project)</label><select name="approval_flag" class="form-select"><option value="0" <?= (($lead['approval_flag']??0)?'':'selected') ?>>No</option><option value="1" <?= (($lead['approval_flag']??0)?'selected':'') ?>>Yes</option></select></div>
<div class="col-md-4"><label>Closed Reason</label><input class="form-control" name="closed_reason" value="<?= e($lead['closed_reason']??'') ?>"></div>
</div>
