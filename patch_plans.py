import re
with open('plans.php', 'r') as f:
    text = f.read()

text = re.sub(
    r'<form action="user_payments.php"[^>]*>[\s\S]*?</form>',
    r'<a href="contact.php" class="plan-btn">Contact to Buy</a>',
    text
)
with open('plans.php', 'w') as f:
    f.write(text)
