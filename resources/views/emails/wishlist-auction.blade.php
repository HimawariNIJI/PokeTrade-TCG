<!DOCTYPE html>
<html>
<body>
    <h2>Great news!</h2>
    <p>A card from your wishlist, <strong class="prism-text font-bold">{{ $cardName }}</strong>, is currently up for auction!</p>
    
    <a href="{{ url('/auctions') }}">Click here to view the auction and place your bid</a>
</body>
</html>