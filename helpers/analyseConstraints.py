from lxml import html
import requests
from collections import OrderedDict
from operator import itemgetter
import time
import codecs

MAX_CONSTRAINT_NUMBER = 2000

#set of all correct written constraint names 
correct_constraints = {"Single value", "Unique value", "Format", "One of", "Symmetric", "Inverse", "Commons link", "Target required claim", "Item", "Type", "Value type", "Range", "Diff within range", "Multi value"}
#"conflicts_with " is correct since the template defines the usage with whitespace, but all the other definitons are without whitespace, so I list every appeareance of conflicts with

#array of wrong written constraint names
wrong_constraints = []

#counters used for statistics
analysed_properties = 0
existing_properties = 0
constraints = {}

#parameters that are used in the templates, initialized with NULL since the values are used to work as SQL statements 
parameters = {
	'base_property': 'NULL',
	'class': 'NULL',
	'classes': 'NULL',
	'exceptions': 'NULL',
	'group by': 'NULL',
	'group property': 'NULL',
	'item': 'NULL',
	'item2': 'NULL',
	'item3': 'NULL',
	'items': 'NULL',
	'list': 'NULL',
	'mandatory': 'NULL',
	'max': 'NULL',
	'min': 'NULL',
	'namespace': 'NULL',
	'pattern': 'NULL',
	'property': 'NULL',
	'relation': 'NULL',
	'required': 'NULL',
	'separate category processing': 'NULL',
	'Tag:[0-9a-z_:]+': 'NULL',
	'value': 'NULL',
	'values_': 'NULL'
}

#time at which analysis begins (for statistics)
start_time = time.time()

# File for SQL statements for constraints and their parameters
sql_file = codecs.open("SQL_constraints_parameters.sql", "w", "utf-8")

#this is how every constraint template begins
search_string = "{{Constraint:"

try:
	#set range of properties to analyse
	for property_number in range(1, MAX_CONSTRAINT_NUMBER+1):
		
		#print property name
		print()
		print("Property " + "{:>5}".format(property_number))
		print(14*"=")
		print()
		
		#increase analysed properties counter
		analysed_properties += 1
		
		#request property talk page from wikidata
		page = requests.get("http://www.wikidata.org/w/index.php?title=Property_talk:P" + str(property_number) + "&action=edit")
		text_string = page.text
		
		#check if property exists
		if text_string.find("Creating Property talk") == -1:
			
			#increase existing properties counter
			existing_properties += 1
			
			#indices for the first and last character belonging to a respective constraint
			start_index = None
			end_index = None
			
			#find beginning of constraint
			start_index = text_string.find(search_string)
			
			#as long as there are more constraints, set new start index and cut off everything before it
			while start_index != -1:
				start_index += len(search_string)
				text_string = text_string[start_index:]

				#match brackets to find end of constraint
				count = 2
				for i, c in enumerate(text_string):
					if c == '{':
						count += 1
					elif c == '}':
						count -= 1
					if count == 0:
						end_index = i-1
						break
				
				#extract constraint
				constraint_string = text_string[:end_index]
				constraint_name = None
				constraint_parameters = None
				
				#if constraint has parameters
				delimiter_index = constraint_string.find('|')
				if delimiter_index != -1:
					
					#set name and parameters accordingly
					constraint_name = constraint_string[:delimiter_index]
					constraint_parameters = constraint_string[delimiter_index+1:]
					
					#delete <nowiki> </nowiki> tags from parameters
					constraint_parameters = constraint_parameters.replace("&lt;nowiki>","").replace("&lt;/nowiki>","")
					
					#delete <!-- --> comments from parameters
					open_index = constraint_parameters.find("&lt;!--")
					while (open_index) != -1:
						close_index = constraint_parameters.find("-->")
						constraint_parameters = constraint_parameters[:open_index] + constraint_parameters[close_index+3:]
						open_index = constraint_parameters.find("&lt;!--")

				#if constraint has no parameters, set name accordingly
				else:
					constraint_name = constraint_string
				
				# build sql statement
				while constraint_parameters != None and constraint_parameters.find('=') != -1:
					first_equal_sign = constraint_parameters.find('=')
					next_equal_sign = constraint_parameters.find('=', first_equal_sign + 1)
					if next_equal_sign == -1:
						next_seperator = len(constraint_parameters)
					else:
						next_seperator = constraint_parameters.rfind('|', first_equal_sign, next_equal_sign)
					if next_seperator == -1:
						next_seperator = len(constraint_parameters)
						if constraint_parameters[:first_equal_sign].strip() == 'values':
							parameters['values_'] = constraint_parameters[first_equal_sign+1:next_seperator]
						else:
							parameters[constraint_parameters[:first_equal_sign].strip()] = constraint_parameters[first_equal_sign+1:next_seperator]
					else:
						next_seperator = next_seperator + 1
						if constraint_parameters[:first_equal_sign].strip() == 'values':
							parameters['values_'] = constraint_parameters[first_equal_sign+1:next_seperator-1]
						else:
							parameters[constraint_parameters[:first_equal_sign].strip()] = constraint_parameters[first_equal_sign+1:next_seperator-1]

					constraint_parameters = constraint_parameters[next_seperator:]

				
				sql_statement = "INSERT \n INTO constraints_from_templates (pid, constraint_name, Tag, base_property, class, classes, exceptions, group_by, group_property, item, item2, item3, items, list, mandatory, max, min, namespace, pattern, property, relation, required, seperate_category_processing, value, values_) \n VALUES (";
				sql_statement += str(property_number) + ", \"" + constraint_name + "\", "
				for par in sorted(parameters):
					if parameters[par] == 'NULL':
						sql_statement += parameters[par].strip() + ", "
					else:
						sql_statement += "\"" + parameters[par].strip() + "\", "
					parameters[par] = 'NULL'

				# delete last ", "
				sql_statement = sql_statement[:len(sql_statement)-2]
				sql_statement += ");\n\n"
				sql_file.write(sql_statement)


				#add constraint to dictionary (counts number of occurrences)
				if constraint_name in constraints:
					constraints[constraint_name] += 1
				else:
					constraints[constraint_name] = 1
				
				#print constraint name and parameters
				try:
					print(constraint_name)
					print(constraint_parameters)
					print()
				except (UnicodeDecodeError, UnicodeEncodeError):
					print("(encoding error)")
					
				#write wrong-written constraint names in array
				if constraint_name not in correct_constraints:
					wrong_constraints.append(constraint_name + ': P' + str(property_number))
					
				#prepare search for new constraint
				text_string = text_string[end_index:]
				start_index = text_string.find(search_string)
			
		#if property does not exist, print message
		else:
			print("(property does not exist)")
			print()
		
#handle interrupts through [ctrl+c]
except KeyboardInterrupt:
	print()
	print("Analysis aborted by user...")
	print()

#time at which analysis ends (for statistics)
end_time = time.time()

#open file, write statistics to it, close file
file = open("constraints_" + time.strftime("%Y%m%d%H%M%S", time.localtime(end_time)) + ".txt", "w")

file.write("RESULTS" + "{:>43}".format(time.asctime(time.localtime(end_time))) + "\n")
file.write(50*"-" + "\n")
file.write("Total number of analysed properties:" + "{:>14}".format(analysed_properties) + "\n")
file.write("Total number of existing properties:" + "{:>14}".format(existing_properties) + "\n")
existing_constraints = 0
for constraint in constraints:
	existing_constraints += constraints[constraint]
file.write("Total number of existing constraints:" + "{:>13}".format(existing_constraints) + "\n")
file.write("Average number of constraints per property:" + "{:>7}".format(round(existing_constraints/existing_properties, 2)) + "\n")
file.write("\n")
sorted_constraints = sorted(constraints.items(), key=itemgetter(1), reverse=True)
for i in range(0, len(sorted_constraints)):
	file.write("{:>4}".format(sorted_constraints[i][1]) + " " + str(sorted_constraints[i][0]) + "\n")
file.write("\n")
file.write("####################################")
file.write("\n")
file.write("\n")
file.write("Possibly wrong written constraint name in template of property xx\n")

wrong_constraints.sort()
for each in wrong_constraints:
	file.write(each + "\n")

file.write("The analysis took " + str(round(end_time-start_time)) + " seconds.\n")
		
file.close()

print("These should be the parameter names: ")
for par in sorted(parameters):
	print(par)
print(len(parameters))
sql_file.close()

#wait for [enter] to close command line
input()