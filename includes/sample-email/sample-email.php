<?php
/**
 * Sample email, auto created on plugin activation
 * @package eMail Manager
 * @author Mucunguzi Ayebare
 */
if ($_SERVER['SCRIPT_FILENAME'] == __FILE__)
    die('Access denied.');
$sample1_title= __('Simple HTML Email Template','wpem');
$sample1_body='
<!-- body -->
<table class="body-wrap">
	<tr>
		<td></td>
		<td class="container" bgcolor="#FFFFFF">
			<!-- content -->
			<div class="content">
			<table>
				<tr>
					<td>
						<p>Hi there,</p>
						<p>Sometimes all you want is to send a simple HTML email with a basic design.</p>
						<h1>Really simple HTML email template</h1>
						<p>This is a really simple email template. It\'s sole purpose is to get you to click the button below.</p>
						<h2>How do I use it?</h2>
						<p>All the information you need is on GitHub.</p>
						<table>
							<tr>
								<td class="padding">
									<p><a href="https://github.com/leemunroe/html-email-template" class="btn-primary">View the source and instructions on GitHub</a></p>
								</td>
							</tr>
						</table>
						<p>Feel free to use, copy, modify this email template as you wish.</p>
						<p>Thanks, have a lovely day.</p>
						<p><a href="http://twitter.com/leemunroe">Follow @leemunroe on Twitter</a></p>
					</td>
				</tr>
			</table>
			</div>
			<!-- /content -->
		</td>
		<td></td>
	</tr>
</table>
<!-- /body -->
<!-- footer -->
<table class="footer-wrap">
	<tr>
		<td></td>
		<td class="container">
			<!-- content -->
			<div class="content">
				<table>
					<tr>
						<td align="center">
							<p>Don\'t like these annoying emails? <a href="#"><unsubscribe>Unsubscribe</unsubscribe></a>.
							</p>
						</td>
					</tr>
				</table>
			</div>
			<!-- /content -->
		</td>
		<td></td>
	</tr>
</table>
<!-- /footer -->';
$sample1_css='*{margin:0;padding:0;font-family:"Helvetica Neue",Helvetica,Helvetica,Arial,sans-serif;font-size:100%;line-height:1.6}img{max-width:100%}body{bgcolor="#f6f6f6";-webkit-font-smoothing:antialiased;-webkit-text-size-adjust:none;width:100%!important;height:100%}a{color:#348eda}.btn-primary{text-decoration:none;color:#FFF;background-color:#348eda;border:solid #348eda;border-width:10px 20px;line-height:2;font-weight:700;margin-right:10px;text-align:center;cursor:pointer;display:inline-block;border-radius:25px}.btn-secondary{text-decoration:none;color:#FFF;background-color:#aaa;border:solid #aaa;border-width:10px 20px;line-height:2;font-weight:700;margin-right:10px;text-align:center;cursor:pointer;display:inline-block;border-radius:25px}.last{margin-bottom:0}.first{margin-top:0}.padding{padding:10px 0}table.body-wrap{width:100%;padding:20px}table.body-wrap .container{border:1px solid #f0f0f0}table.footer-wrap{width:100%;clear:both!important}.footer-wrap .container p{font-size:12px;color:#666}table.footer-wrap a{color:#999}h1,h2,h3{font-family:"Helvetica Neue",Helvetica,Arial,"Lucida Grande",sans-serif;color:#000;margin:40px 0 10px;line-height:1.2;font-weight:200}h1{font-size:36px}h2{font-size:28px}h3{font-size:22px}ol,p,ul{margin-bottom:10px;font-weight:400;font-size:14px}ol li,ul li{margin-left:5px;list-style-position:inside}.container{display:block!important;max-width:600px!important;margin:0 auto!important;clear:both!important}.body-wrap .container{padding:20px}.content{max-width:600px;margin:0 auto;display:block}.content table{width:100%}';