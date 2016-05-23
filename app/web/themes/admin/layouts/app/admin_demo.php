<?php
HTMLRendering::addJSURL('https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js');
HTMLRendering::addCSSURL('https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css');
HTMLRendering::addJSURL('https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js');
HTMLRendering::addThemeJSFile('admin_demo.js');
HTMLRendering::useLayout('page_skeleton');
?>
<div class="row">
	<div class="col-lg-12">
<!-- 		<h1>Dashboard <small>Statistics Overview</small></h1> -->
<!-- 		<ol class="breadcrumb"> -->
<!-- 			<li class="active"><i class="fa fa-dashboard"></i> Dashboard</li> -->
<!-- 		</ol> -->
		<div class="alert alert-success alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
Welcome to Orpheus Admin Panel Demo, this SB Admin Theme is designed by <a class="alert-link" href="http://startbootstrap.com">Start Bootstrap</a> and integrated by Florent HAZARD !
Feel free to use this plugin and its templates for your admin needs! We are using a few different plugins to handle the dynamic tables and charts, so make sure you check out the necessary documentation links provided.<br />
All accesses to the admin's pages are restricted by permissions, this is why you could not access to all pages with your member role.
		</div>
	</div>
</div><!-- /.row -->

<div class="row">
	<div class="col-lg-3 col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-3">
						<i class="fa fa-comments fa-5x"></i>
					</div>
					<div class="col-xs-9 text-right">
						<div class="huge">26</div>
						<div>New Comments!</div>
					</div>
				</div>
			</div>
			<a href="#">
				<div class="panel-footer">
					<span class="pull-left">View Details</span>
					<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
					<div class="clearfix"></div>
				</div>
			</a>
		</div>
	</div>
	<div class="col-lg-3 col-md-6">
		<div class="panel panel-green">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-3">
						<i class="fa fa-tasks fa-5x"></i>
					</div>
					<div class="col-xs-9 text-right">
						<div class="huge">12</div>
						<div>New Tasks!</div>
					</div>
				</div>
			</div>
			<a href="#">
				<div class="panel-footer">
					<span class="pull-left">View Details</span>
					<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
					<div class="clearfix"></div>
				</div>
			</a>
		</div>
	</div>
	<div class="col-lg-3 col-md-6">
		<div class="panel panel-yellow">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-3">
						<i class="fa fa-shopping-cart fa-5x"></i>
					</div>
					<div class="col-xs-9 text-right">
						<div class="huge">124</div>
						<div>New Orders!</div>
					</div>
				</div>
			</div>
			<a href="#">
				<div class="panel-footer">
					<span class="pull-left">View Details</span>
					<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
					<div class="clearfix"></div>
				</div>
			</a>
		</div>
	</div>
	<div class="col-lg-3 col-md-6">
		<div class="panel panel-red">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-3">
						<i class="fa fa-support fa-5x"></i>
					</div>
					<div class="col-xs-9 text-right">
						<div class="huge">13</div>
						<div>Support Tickets!</div>
					</div>
				</div>
			</div>
			<a href="#">
				<div class="panel-footer">
					<span class="pull-left">View Details</span>
					<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
					<div class="clearfix"></div>
				</div>
			</a>
		</div>
	</div>
</div>
<?php /*
<div class="row">
	<div class="col-lg-3">
		<div class="panel panel-info">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-6">
						<i class="fa fa-comments fa-5x"></i>
					</div>
					<div class="col-xs-6 text-right">
						<p class="announcement-heading">456</p>
						<p class="announcement-text">New Mentions!</p>
					</div>
				</div>
			</div>
			<a href="#">
				<div class="panel-footer announcement-bottom">
					<div class="row">
						<div class="col-xs-6">
							View Mentions
						</div>
						<div class="col-xs-6 text-right">
							<i class="fa fa-arrow-circle-right"></i>
						</div>
					</div>
				</div>
			</a>
		</div>
	</div>
	<div class="col-lg-3">
		<div class="panel panel-warning">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-6">
						<i class="fa fa-check fa-5x"></i>
					</div>
					<div class="col-xs-6 text-right">
						<p class="announcement-heading">12</p>
						<p class="announcement-text">To-Do Items</p>
					</div>
				</div>
			</div>
			<a href="#">
				<div class="panel-footer announcement-bottom">
					<div class="row">
						<div class="col-xs-6">
							Complete Tasks
						</div>
						<div class="col-xs-6 text-right">
							<i class="fa fa-arrow-circle-right"></i>
						</div>
					</div>
				</div>
			</a>
		</div>
	</div>
	<div class="col-lg-3">
		<div class="panel panel-danger">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-6">
						<i class="fa fa-tasks fa-5x"></i>
					</div>
					<div class="col-xs-6 text-right">
						<p class="announcement-heading">18</p>
						<p class="announcement-text">Crawl Errors</p>
					</div>
				</div>
			</div>
			<a href="#">
				<div class="panel-footer announcement-bottom">
					<div class="row">
						<div class="col-xs-6">
							Fix Issues
						</div>
						<div class="col-xs-6 text-right">
							<i class="fa fa-arrow-circle-right"></i>
						</div>
					</div>
				</div>
			</a>
		</div>
	</div>
	<div class="col-lg-3">
		<div class="panel panel-success">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-6">
						<i class="fa fa-comments fa-5x"></i>
					</div>
					<div class="col-xs-6 text-right">
						<p class="announcement-heading">56</p>
						<p class="announcement-text">New Orders!</p>
					</div>
				</div>
			</div>
			<a href="#">
				<div class="panel-footer announcement-bottom">
					<div class="row">
						<div class="col-xs-6">
							Complete Orders
						</div>
						<div class="col-xs-6 text-right">
							<i class="fa fa-arrow-circle-right"></i>
						</div>
					</div>
				</div>
			</a>
		</div>
	</div>
</div><!-- /.row -->
*/ ?>

<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><i class="fa fa-bar-chart-o"></i> Traffic Statistics: October 1, 2013 - October 31, 2013</h3>
			</div>
			<div class="panel-body">
				<div id="morris-area-chart"></div>
			</div>
		</div>
	</div>
</div><!-- /.row -->

<div class="row">
	<div class="col-lg-4">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><i class="fa fa-long-arrow-right"></i> Traffic Sources: October 1, 2013 - October 31, 2013</h3>
			</div>
			<div class="panel-body">
				<div id="morris-donut-chart"></div>
				<div class="text-right">
					<a href="#">View Details <i class="fa fa-arrow-circle-right"></i></a>
				</div>
			</div>
		</div>
	</div>
	<div class="col-lg-4">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><i class="fa fa-clock-o"></i> Recent Activity</h3>
			</div>
			<div class="panel-body">
				<div class="list-group">
					<a href="#" class="list-group-item">
						<span class="badge">just now</span>
						<i class="fa fa-calendar"></i> Calendar updated
					</a>
					<a href="#" class="list-group-item">
						<span class="badge">4 minutes ago</span>
						<i class="fa fa-star"></i> Code the best PHP framework ever
					</a>
					<a href="#" class="list-group-item">
						<span class="badge">23 minutes ago</span>
						<i class="fa fa-truck"></i> Order 392 shipped
					</a>
					<a href="#" class="list-group-item">
						<span class="badge">46 minutes ago</span>
						<i class="fa fa-money"></i> Invoice 653 has been paid
					</a>
					<a href="#" class="list-group-item">
						<span class="badge">1 hour ago</span>
						<i class="fa fa-user"></i> A new user has been added
					</a>
					<a href="#" class="list-group-item">
						<span class="badge">2 hours ago</span>
						<i class="fa fa-check"></i> Completed task: "pick up dry cleaning"
					</a>
					<a href="#" class="list-group-item">
						<span class="badge">yesterday</span>
						<i class="fa fa-globe"></i> Saved the world
					</a>
					<a href="#" class="list-group-item">
						<span class="badge">two days ago</span>
						<i class="fa fa-check"></i> Completed task: "fix error on sales page"
					</a>
				</div>
				<div class="text-right">
					<a href="#">View All Activity <i class="fa fa-arrow-circle-right"></i></a>
				</div>
			</div>
		</div>
	</div>
	<div class="col-lg-4">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><i class="fa fa-money"></i> Recent Transactions</h3>
			</div>
			<div class="panel-body">
				<div class="table-responsive">
					<table class="table table-bordered table-hover table-striped tablesorter">
						<thead>
							<tr>
								<th>Order # <i class="fa fa-sort"></i></th>
								<th>Order Date <i class="fa fa-sort"></i></th>
								<th>Order Time <i class="fa fa-sort"></i></th>
								<th>Amount (USD) <i class="fa fa-sort"></i></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>3326</td>
								<td>10/21/2013</td>
								<td>3:29 PM</td>
								<td>$321.33</td>
							</tr>
							<tr>
								<td>3325</td>
								<td>10/21/2013</td>
								<td>3:20 PM</td>
								<td>$234.34</td>
							</tr>
							<tr>
								<td>3324</td>
								<td>10/21/2013</td>
								<td>3:03 PM</td>
								<td>$724.17</td>
							</tr>
							<tr>
								<td>3323</td>
								<td>10/21/2013</td>
								<td>3:00 PM</td>
								<td>$23.71</td>
							</tr>
							<tr>
								<td>3322</td>
								<td>10/21/2013</td>
								<td>2:49 PM</td>
								<td>$8345.23</td>
							</tr>
							<tr>
								<td>3321</td>
								<td>10/21/2013</td>
								<td>2:23 PM</td>
								<td>$245.12</td>
							</tr>
							<tr>
								<td>3320</td>
								<td>10/21/2013</td>
								<td>2:15 PM</td>
								<td>$5663.54</td>
							</tr>
							<tr>
								<td>3319</td>
								<td>10/21/2013</td>
								<td>2:13 PM</td>
								<td>$943.45</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="text-right">
					<a href="#">View All Transactions <i class="fa fa-arrow-circle-right"></i></a>
				</div>
			</div>
		</div>
	</div>
</div><!-- /.row -->
