# test_script.py
import os
import pytest
from playwright.sync_api import sync_playwright
base_url =os.environ.get('BASE_URL')
print(os.environ)
print(base_url," URL")
#base_url = "http://10.11.51.100/test.html"  # Replace with your actual base URL


@pytest.fixture(scope="function")
def page():
    with sync_playwright() as p:
        browser = p.chromium.launch()
        context = browser.new_context()
        page = context.new_page()
        yield page
        browser.close()

def test_check_index_html_title(page):
    # Navigate to the index.html page
    page.goto(base_url)
    
    # Get the title of the page
    title = page.title()
    
    # Assert the title
    assert title == 'Hello KMIT!'

def test_check_index_html_title_again(page):
    # Navigate to the index.html page
    page.goto(base_url)
    
    # Get the title of the page
    title = page.title()
    
    # Assert the title
    assert title == 'Hello KMIT!'
