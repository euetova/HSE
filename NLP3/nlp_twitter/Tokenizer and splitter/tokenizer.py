#токенизатор основан на токенизаторах с открытым кодом, в частности nltk и christopherpotts

import re

def take_abbs():
	f = open('abbs.txt', 'r', encoding='utf-8')
	abb = [line.strip('\n') for line in f]
	abb = [re.escape(x) for x in abb]
	abbs = '(?:[ ^])' + '|'.join(abb) + '(?:[ \)$])'
	f.close()
	return abbs
ABRS = take_abbs()

#urls - nltk version
URLS = r"""         # Capture 1: entire matched URL
  (?:
  https?:               # URL protocol and colon
    (?:
      /{1,3}                # 1-3 slashes
      |                 #   or
      [a-z0-9%]             # Single letter or digit or '%'
                                       # (Trying not to match e.g. "URI::Escape")
    )
    |                   #   or
                                       # looks like domain name followed by a slash:
    [a-z0-9.\-]+[.]
    (?:[a-z]{2,13})
    /
  )
  (?:                   # One or more:
    [^\s()<>{}\[\]]+            # Run of non-space, non-()<>{}[]
    |                   #   or
    \([^\s()]*?\([^\s()]+\)[^\s()]*?\) # balanced parens, one level deep: (...(...)...)
    |
    \([^\s]+?\)             # balanced parens, non-recursive: (...)
  )+
  (?:                   # End with:
    \([^\s()]*?\([^\s()]+\)[^\s()]*?\) # balanced parens, one level deep: (...(...)...)
    |
    \([^\s]+?\)             # balanced parens, non-recursive: (...)
    |                   #   or
    [^\s`!()\[\]{};:'".,<>?«»“”‘’]  # not a space or one of these punct chars
  )
  |                 # OR, the following to match naked domains:
  (?:
    (?<!@)                  # not preceded by a @, avoid matching foo@_gmail.com_
    [a-z0-9]+
    (?:[.\-][a-z0-9]+)*
    [.]
    (?:[a-z]{2,13})
    \b
    /?
    (?!@)                   # not succeeded by a @,
                            # avoid matching "foo.na" in "foo.na@example.com"
  )
"""

# Twitter specific:
HASHTAG = r"""(?:\#\w+)"""
TWITTER_USER = r"""(?:@\w+)"""
EMOTICONS = r"""
   (?:
     [<>]?
     [:;=8]                     # eyes
     [\-o\*\']?                 # optional nose
     [\)\]\(\[dDpP*/\:\}\{@\|\\] # mouth
     |
     [\)\]\(\[dDpPсСрР/\:\}\{@\|\\] # mouth
     [\-o\*\']?                 # optional nose
     [:;=8]                     # eyes
     [<>]?
   )"""
#separately compiled regexps
TWITTER_USER_RE = re.compile(TWITTER_USER, re.UNICODE)
HASHTAG_RE = re.compile(HASHTAG, re.UNICODE)

# more regular expressions for word compilation, borrowed from nltk
#phone numbers
PHONE = r"""[ \-\(\)\d]{6,}"""
FIN = r"""(?:[\.\?!]{1,})"""
# email addresses
EMAILS = r"""[\w.+-]+@[\w-]+\.(?:[\w-]\.?)+[\w-]"""
#long non-word, non-numeric repeats
# Remaining word types:
#PART-OF-WORD = r"""(?:\S*�)"""
GEO = r"""(?:I'm at .*? in .*?(?: w/ @[\w])?|\(@ .*?\))"""
WORDS = r"""
    \w+(?:-\w+)+                    #(?:[^\W\d_](?:[^\W\d_]|['\-_])+[^\W\d_]) # Words with apostrophes or dashes.
    |
    (?:[+\-]?\d+[,/.:-]\d+[+\-]?)  # Numbers, including fractions, decimals.
    |
    (?:[\w_]+)                     # Words without apostrophes or dashes.
	|
	[\n]
    |
    (?:\S)                         # Everything else that isn't whitespace.
    """
TWITTER_REGEXPS = [ABRS, GEO, EMOTICONS, FIN, URLS, PHONE, TWITTER_USER, HASHTAG, EMAILS, WORDS]

class TweetTokenizer():

    def __init__(self):
        self.WORD_RE = re.compile(r"""(%s)""" % "|".join(TWITTER_REGEXPS), re.VERBOSE | re.I | re.UNICODE)

    def tokenize(self, text):
        words = self.WORD_RE.findall(text)
        return words
