#!/usr/bin/env python
#encoding:utf8

import threading,paramiko,random,string,sys,json

lower = string.lowercase    #生成密码的小写字母范围
upper = string.uppercase    #大写字母范围
number = range(0,10)        #数字范围
other = '!@#%^&*()'         #特殊符号范围
length = 10                 #设置随机密码的长度
timeout = 3                 #设置执行的操作超时时间

# json 格式传参例子
# '[{"ipaddr":"192.168.2.202","rootpwd":"123","username":["root","cihi2"],"passwd":["",""],"port":22}, 
#   {"ipaddr":"192.168.2.203","rootpwd":"Ke#%9","username":["root","cihi"],"passwd":["123","123"],"port":22}]'

def getsession(host, username, passwd, port):
    """创建到主机的连接"""
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        ssh.connect(host, int(port), username, passwd, timeout=3)
        sftp = ssh.open_sftp()
        err = ''
    except Exception,e:
        err = 'Connect to %s failed: %s' % (host,str(e))
        ssh,sftp = '',''
    return ssh,sftp,err


def runcmd(host, passwd_length, timeout):
    """执行修改密码的操作"""
    username_list = host['username']
    rootpwd = host['rootpwd']
    password_list = host['passwd']
    port = host['port']
    ipaddr = host['ipaddr']
    ssh,sftp,error_info = getsession(ipaddr, "root", rootpwd, port)
    exec_info = []
    
    for user, pwd in zip(username_list, password_list):
        info = {"username": user, "status": "failed", "old_pwd": rootpwd}
        if not error_info:
            if not pwd:
                newpasswd = createpasswd(passwd_length)
            else:
                newpasswd = pwd
            command = "echo '{0}' | /usr/bin/passwd {1} --stdin".format(newpasswd, user)
            try:
                stdin,stdout,stderr = ssh.exec_command(command)
            except Exception as err:
                info.update({"message": "{0}(default timeout {1} s).".format(err, timeout)})
            else:
                r_info = stderr.read().strip()
                if not r_info:
                    info.update({"status": "success", "message": newpasswd})
                else:
                    info.update({"message": r_info})
        else:
            info.update({"message": error_info})
        exec_info.append(info)
    if not error_info:
        ssh.close()
        sftp.close()
    result.append({"ipaddr": ipaddr, "info": exec_info})

def createpasswd(length):
    """生成随机新密码的函数"""
    newpasswd = ""
    for i in xrange(length):
        cos = random.randint(1,4)
        if cos == 1:
            newpasswd += random.choice(lower)
        elif cos == 2:
            newpasswd += random.choice(upper)
        elif cos == 3:
            newpasswd += str(random.choice(number))
        else:
            newpasswd += random.choice(other)
    return newpasswd



def main():
    threads = []
    for host in host_list:
        threads.append(threading.Thread(target=runcmd, args=(host, length, timeout)))
    for t in threads:
        t.start()
    for t in threads:
        t.join()
    print json.dumps(result)

if __name__=="__main__":
	# 存放执行结果
    result = []
    # 接收传递第一个参数
    args = sys.argv[1]
    # 解析json格式的参数
    host_list = json.loads(args)
    main()
