import unittest

def to_table(snippets, word):
	""" create table using 'format' methods
    :param snippets - snippets
    :param word: str - keyword
    :return: array - table of snippets
	"""
	table = []
	len_word = len(word)
	max_snippets = max([len(snippets[i][0]) for i in range(len(snippets))])	
	for i in range(len(snippets)):
		len_snippets = len(snippets[i][0])
		row = '{:>{max_snippets}}	{:^{len_word}}	{}'.format(snippets[i][0], word, snippets[i][2], max_snippets = max_snippets, len_word = len_word)
		table.append(row+'\n')
	return table


def kwiq(word, text, num=3):
	""" create snippets
    :param word: str - keyword
    :param text: string - text 
    :param num: - int - length of contexts of snippets
    :return: array - table of snippets
    """
	lst = text.lower().split()
	positions = []
	for i in range(len(lst)):
		if lst[i] == word:
			positions.append(i)
	snippets = [['' for i in range(num)] for p in range(len(positions))]
	for i in range(len(snippets)):
		if positions[i] < num+1:
			snippets[i][0] = ' '.join(lst[:positions[i]])
		else:
			snippets[i][0] = ' '.join(lst[positions[i]-num:positions[i]])
		snippets[i][1] = word
		if positions[i] > len(lst)-(num+2):
			snippets[i][2] = ' '.join(lst[positions[i]+1:])
		else:
			snippets[i][2] = ' '.join(lst[positions[i]+1:positions[i]+1+num])
	return to_table(snippets, word)

	
class test_kwiq_TestCase(unittest.TestCase):
	"""Tests for kwiq"""
	def test_no_context(self):
		self.assertEqual(('\tword\t\n'), kwiq('word', 'word'))
	def test_no_word_in_text(self):
		with self.assertRaises(ValueError):
			kwiq('word', 'All you need is love')


print(kwiq('you', 'Nothing you can do but you can learn how to be you in time. All you need is love. Love is all you need.', 4))

if __name__ == "__main__":
    unittest.main()
