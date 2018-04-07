from pattern.web import Wikipedia, plaintext
import string
import re
import nltk
from nltk.util import ngrams
from collections import Counter, defaultdict
from unittest import *
from numpy import log


class WikiParser:
    def __init__(self):
        pass

    def text_cleaning(self, text):
        exclude = '"#$%&\'()*+,-/:;<=>?@[\\]^_`{|}~–«»'        
        text = ''.join([ch for ch in text if ch not in exclude])
        text = re.sub('\s{2,}',' ', text)
        text = text.lower()
        return text
        
    def get_articles(self, start, depth=1, max_count=1):
        article = Wikipedia().article(start)
        links = article.links
        list_of_strings = []
        for link in links:
            text = Wikipedia().article(link)
            text = self.text_cleaning(plaintext(text.source))
            list_of_strings.append(text)
        return list_of_strings
		
class TextStatistics:
    def __init__(self, articles):
        self.articles = articles
     
    def get_top_3grams(self, n, use_idf=False):
        all_3grams = []
        all_sentences = []
        for a in self.articles:
            all_sentences += [x for x in re.split('[.?!]',a) if x]
        num_sentences = len(all_sentences)
        for sent in all_sentences:                                         
            all_3grams += ngrams(sent, 3)
        freqdist = nltk.FreqDist(all_3grams)
        if use_idf == True and num_sentences > 0:
            for ngram in freqdist.keys():
                sent_freq = 0
                for sent in all_sentences:
                    if ''.join(ngram) in sent:
                        sent_freq += 1
                idf = log(num_sentences/sent_freq)
                freqdist[ngram] *= idf
        most_common_n = sorted(freqdist.items(), key=lambda x: (-x[1], x[0]))[:n]
        list_of_3grams_in_descending_order_by_freq = [ng for ng, fr in most_common_n]
        list_of_their_corresponding_freq = [fr for ng, fr in most_common_n]
        return (list_of_3grams_in_descending_order_by_freq, list_of_their_corresponding_freq)
    
    def get_top_words(self, n, use_idf=False):
        num_articles = len(self.articles)
        all_words = []
        stop_words = ['a', 'an', 'the', 'as', 'in', 'out', 'on', 'off', 'until', 'of', 'at', 'by', 'for', 'with', 
                      'between', 'into', 'through', 'during', 'before', 'after', 'above', 'below', 'and', 'but', 'or',
                      'to', 'from', 'up', 'down', 'since', 'over', 'under', 'about', 'against', 'like', 'via', 'not']
        for a in self.articles:
            all_words += [x for x in a.split() if x not in stop_words and re.search('[0-9]', x) is None]
        freqdist = nltk.FreqDist(all_words)
        if use_idf == True and num_articles > 0:
            for word in freqdist.keys():
                art_freq = 0
                for a in self.articles:
                    if word in a:
                        art_freq += 1
                idf = log(num_articles / art_freq)
                freqdist[word] *= idf
        most_common_n = sorted(freqdist.items(), key=lambda x: (-x[1], x[0]))[:n]
        list_of_words_in_descending_order_by_freq = [w for w, fr in most_common_n]
        list_of_their_corresponding_freq = [fr for w, fr in most_common_n]
        return (list_of_words_in_descending_order_by_freq, list_of_their_corresponding_freq)

class TestCase(TestCase):
    
    def test_no_articles(self):
        x = TextStatistics([])
        self.assertFalse(any(x.get_top_3grams(5,False)))
        self.assertFalse(any(x.get_top_words(5,False)))

    def test_stop_word(self):
        x = TextStatistics(['the dog', 'dog bites'])
        self.assertEqual(x.get_top_3grams(1, True), ([(' ', 'b', 'i')], [0.69314718055994529]))
        self.assertEqual(x.get_top_words(2, True), (['bites', 'dog'], [0.69314718055994529, 0.0]))

		
class Experiment:
    def __init__(self, article):
        self.article = article
        self.parser = WikiParser()
        
    def show_results(self):
        statistics_links = TextStatistics(self.parser.get_articles(self.article))
        top_3grams_links = statistics_links.get_top_3grams(20, use_idf=True)
        top_words_links = statistics_links.get_top_words(20, use_idf=True)
        print('For links in article\nTop 20 3grams:')
        print('\n'.join([''.join(w)+' : '+str(n) for w, n in zip(top_3grams_links[0], top_3grams_links[1])]))
        print('\nTop 20 words:')
        print('\n'.join([w+' : '+str(n) for w, n in zip(top_words_links[0], top_words_links[1])]))
		
x = Experiment('Natural language processing')
x.show_results()

'''
For links in article
Top 20 3grams:
 th : 49976.1410803
the : 46444.5501735
he  : 40814.2074899
ion : 37712.2254205
tio : 35500.6811292
ing : 34657.4426158
 in : 34523.4698156
on  : 33549.328879
ati : 32370.6447653
 of : 32148.7725222
ng  : 32093.3997806
of  : 31353.0018229
 an : 31123.8509963
ed  : 31046.1035472
al  : 30610.1278795
 co : 30469.6479865
es  : 29738.0032346
and : 29097.0015367
nd  : 29002.5357694
ent : 28019.3644713

Top 20 words:
displaystyle : 2394.13465625
turing : 1110.90413677
arabic : 979.551918693
european : 745.57318802
chomsky : 735.770115441
retrieved : 714.642586481
learning : 710.98665651
languages : 663.876270197
german : 658.22713732
union : 644.821085078
spanish : 642.551153727
english : 634.060113303
verbs : 631.358309012
turkish : 631.156439591
french : 611.723703662
dialects : 609.55737832
chinese : 578.716865489
quantum : 574.676258901
japanese : 564.717580403
barsky : 557.998126064
'''
