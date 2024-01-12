print("This line will be printed.")
x = 15
if x == 1:
    # indented four spaces
    print("x is 1.")
myfloat = 7.0
print(myfloat)
myfloat2 = float(7)
print(myfloat2)
mylist = [1, 2, 3, ]
mylist.append(1)
mylist.append(2)
mylist.append(3)
mylist[2] = 9
mylist[5] = 11
mylist[4] = 91

print(mylist[0]) # prints 1
print(mylist[1]) # prints 2
print(mylist[2]) # prints 3

# prints out 1,2,3
for x in mylist:
    print(x)

# This prints out "John is 23 years old."
name = "John"
age = 23
print("%s is %d years old." % (name, age))

vTest = False
if vTest :
    print('Goed zo')
else: 
    print("NIET goed")
