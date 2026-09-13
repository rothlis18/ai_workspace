# AI Workspace - Question Auction Bidding Engine

External bidding engine for the VRAM Overruns multi-agent LLM benchmark system.

## Features

- **Live Auction Interface** - Real-time bidding for question processing slots
- **Khmer Time Countdown** - Automatic sync to Thailand (UTC+7) timezone
- **Balance System** - User account balances tracked in `balance.txt`
- **Question Submission** - AI-verified question intake before benchmark processing
- **Bid History** - Real-time live feed of all bids
- **Responsive UI** - Works on desktop and mobile devices

## Setup

1. Deploy `index.html` and `api.php` to web server
2. Create `data/` directory (auto-created on first run)
3. Populate `balance.txt` with user balances
4. Access via `http://your-domain/index.html`

## Files

- **index.html** - Frontend UI with live countdown and bidding form
- **api.php** - Backend API for auction management and bid processing
- **balance.txt** - User balance ledger (username:amount)
- **data/auction.json** - Current active auction data
- **data/bids.json** - Historical bid records
- **data/submitted_questions.json** - Pending question submissions

## API Endpoints

### GET
- `?action=getCurrentAuction` - Get current auction and recent bids
- `?action=getBalance&username=USER` - Get user balance and bid count

### POST
- `action=placeBid` - Place a new bid (requires: username, amount, optional question)
- `action=submitQuestion` - Submit a question for review (requires: username, question)
- `action=initAuction` - Initialize default auction

## Processing Schedule

**Time:** 12:00 Noon (Khmer/Bangkok Time, UTC+7)
**Frequency:** Daily
**Models:** Full 10-model permutation matrix
**Output:** Benchmark results stored in vram_overuns repository

## Next Steps

- Add admin panel for balance management
- Implement AI question validation via LLM
- Add email notifications for auction winners
- Create leaderboard of top bidders
