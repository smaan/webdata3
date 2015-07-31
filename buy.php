<!--Sukhbir Singh
1001081404
http://omega.uta.edu/~sxs1404/project3/buy.php
-->
<?php
/*start session and set session variables using global variable $_SESSION
Session_start() function must appear BEFORE the <html> tag.
Session variables are not passed individually to each new page, instead they are retrieved from the session we open at the beginning of each page (session_start())
All session variable values are stored in the global $_SESSION variable*/
session_start();
//fill the session category
if(isset($_GET['category'])){
	$_SESSION['category']=$_GET['category'];
}
//fill the session search variable with user input keyword
if(isset($_GET['search'])){
	$_SESSION['search']=$_GET['search'];
}
//delete the record from session
if($_GET['delete']){
	foreach($_SESSION['buy'] as $i=>$ps){
		if($ps == $_GET['delete']){
			$_SESSION['buy'][$i] = "";
		}
	}
}
//add a product to basket from session, if it is not there 
if($_GET['buy']){
	foreach($_SESSION['buy'] as $i=>$ps){
		if($ps == $_GET['buy']){
			$_SESSION['buy'][$i] = "";
		}
	}
	$_SESSION['buy'][]=$_GET['buy'];
}
if(isset($_GET['clear']) && $_GET['clear'] == 1){
	session_unset(); 
	$_SESSION['category']="";
	$_SESSION['search'] = "";
}
?>

<html>
<head><title>Buy Products</title></head>
<body>
Shopping Basket:</p>
<?php
if($_SESSION['buy']){
?>
<table border=1>
	<tr>
		<td>Image</td>
		<td>Name</td>
		<td>Price</td>
		<td>Action</td>		
	</tr>
<?php
	//on selection of a product, add the price to total, and create a new row in shopping basket
	foreach($_SESSION['buy'] as $buy){
		//gets the product specification for each product selected
		$product = file_get_contents("http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/GeneralSearch?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&trackingId=7000610&productId=".$buy."&showProductSpecs=true");
		$xml_product = new SimpleXMLElement($product);
		//echo "<pre>";
		//print_r($xml_product);
		//echo "</pre>";
		foreach ($xml_product->categories->category->items->product as $ps) {
			$total_price += floatval($ps->minPrice);
		?>
		<tr>
			<td><img src="<?php echo $ps->images->image[0]->sourceURL?>"/></td>
			<td><?php echo $ps->name;?></td>
			<td align=right><?php echo $ps->minPrice;?></td>
			<td><a href="buy.php?delete=<?php echo $ps['id'];?>">Delete</a></td>
		</tr>
<?php
		}
	}
?>
</table>
<p>Total: $<?php echo $total_price;?><p/>
<?php 
}
?>

<form name="clear_form" action="buy.php" method="GET">
<input type="hidden" name="clear" value="1"/>
<input type="submit" value="Empty Basket"/>
</form>

<p/>
<?php
error_reporting(E_ALL);
ini_set('display_errors','On');

//create an xml file with contents of category ID = 72
$category = file_get_contents("http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/CategoryTree?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&trackingId=7000610&categoryId=72&showAllDescendants=true");
if($category){
	$xmlcategory = new SimpleXMLElement($category);
}
//print_r to see value. <pre> tag for prefromated text
/*echo "<pre>";
print_r($_SESSION);
print_r($_GET);
echo "</pre>";*/

//urlencode is used so that we can replace the white space between the input values with '+'
//append category if exists else leave blank; urlencode keywords and append if exist else leave blank, to API key to fetch that data in $search string
$search = file_get_contents("http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/GeneralSearch?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&trackingId=7000610&category=".(isset($_SESSION['category'])?$_SESSION['category']:'')."&keyword=".(isset($_SESSION['search'])?urlencode($_SESSION['search']):'')."&numItems=20");
if($search) {
	//convert string to xml format
	$xmlsearch = new SimpleXMLElement($search);
}
?>

<!--when a user enters keyword and click on submit, GET method collects data and buy.php is again loaded-->
<form name="search_form" action="buy.php" method="GET">
<fieldset><legend>Find products:</legend>
<label>Category: 
<select name="category" id="category">
<?php
//create dropdown list
if($category){
	foreach ($xmlcategory->category->categories->category as $cat_a1) {
		$cat_a2 = $cat_a1->name;
?>
	<option value= "<?php echo $cat_a1['id'];?>"<?php echo (isset($_SESSION['category']) && $_SESSION['category'] == $cat_a1['id']) ? "selected" : ""?>><?php echo $cat_a2;?>
	</option>
	<optgroup label= "<?php echo $cat_a2;?>">
	<?php foreach ($cat_a1->categories->category as $cat_b1) {
		$cat_b2 = $cat_b1->name;
	?>
		<!--if from dropdown an entry is selected, put its name in selected : name -->
		<option value= "<?php echo $cat_b1['id'];?>"<?php echo (isset($_SESSION['category']) && $_SESSION['category'] == $cat_b1['id']) ? "selected" : ""?>><?php echo $cat_b2;?>
		</option>
	<?php }?>
	</optgroup>
<?php }
}
?>
</select>
</label>
<!-- if page is reloaded and search keyword was same , it checks from session. if session has keyword, displayd that else empty -->
<label>Search keywords: <input type="text" name="search" value="<?php echo (isset($_SESSION['search']) ? $_SESSION['search'] : '');?>"/><label>
<input type="submit" value="Search"/>
</fieldset>
</form>

<p/>
<?php if($search){?>
<!-- when search is clicked, fill display area with all matching records -->
<table border=1>
<tr>
	<td width="10%">Image</td>
	<td width="10%">Name</td>
	<td width="5%">Price</td>
	<td>Description</td>
</tr>
<?php
foreach ($xmlsearch->categories->category->items->product as $prod) {
?>
	<tr>
		<td><a href="buy.php?buy=<?php echo $prod['id'];?>"><img src="<?php echo $prod->images->image[0]->sourceURL?>"/></a></td>
		<td><?php echo $prod->name;?></td>
		<td align=right><?php echo $prod->minPrice;?></td>
		<td><?php echo ($prod->fullDescription ? $prod->fullDescription : "Description not available.");?></td>
	</tr>
<?php }?>
</table>
<?php }?>
</body>
</html>