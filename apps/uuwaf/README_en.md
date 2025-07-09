# UUSEC WAF

**UUSEC WAF** Web Application Firewall is an industrial grade free, high-performance, and highly scalable web application and API security protection product that supports AI and semantic engines. It is a comprehensive website protection product launched by UUSEC Technology, which first realizes the three-layer defense function of traffic layer, system layer, and runtime layer.

## Main Features:

:ophiuchus: Intelligent 0-day defense

UUSEC WAF innovatively applies machine learning technology, using anomaly detection algorithms to distinguish and identify HTTP normal and attack traffic, and models whitelist threats to normal traffic. By using machine learning algorithms to automatically learn the parameter characteristics of normal traffic and convert them into corresponding parameter whitelist rule libraries, it is possible to intercept attacks without adding rules when facing various sudden 0-day vulnerabilities, eliminating the pain of website managers having to work late to upgrade as soon as vulnerabilities appear.

:taurus: Ultimate CDN acceleration

UUSEC self-developed cache cleaning feature surpasses the arbitrary cache cleaning function only available in the commercial version of nginx, proxy_cache_purge. The commercial version of nginx only supports * pattern matching to clean the cache, while UUSEC WAF further supports regular expression matching URL path cache cleaning, which has higher flexibility and practicality compared to the commercial version of nginx. Users can enjoy ultimate CDN acceleration while more conveniently solving cache expiration issues.

:virgo: Powerful proactive defense

The self-developed 'HIPS' and 'RASP' functions of UUSEC WAF can achieve more powerful dual layer defense at the system layer and application runtime layer, effectively preventing zero day vulnerability attacks. Host layer active defense can intercept low-level attacks at the system kernel layer, such as restricting process network communication, process creation, file read and write, system privilege escalation, system overflow attacks, etc. Runtime application self-defense RASP is inserted into runtime engines such as Java JVM and PHP Zend to effectively track runtime context and intercept various web 0-day vulnerability attacks.

:libra: Advanced semantic engine

UUSEC WAF adopts four industry-leading semantic analysis based detection engines, namely SQL, XSS, RCE, and LFI. Combined with multiple deep decoding engines, it can truly restore HTTP content such as base64, JSON, and form data, effectively resisting various attack methods that bypass WAF. Compared with traditional regular matching, it has the characteristics of high accuracy, low false alarm rate, and high efficiency. Administrators do not need to maintain a complex rule library to intercept multiple types of attacks.

:gemini: Advanced rule engine

UUSEC WAF actively utilizes the high-performance and highly flexible features of nginx and luajit. In addition to providing a traditional rule creation mode that is user-friendly for ordinary users, it also offers a highly scalable and flexible Lua script rule writing function, allowing advanced security administrators with certain programming skills to create a series of advanced vulnerability protection rules that traditional WAF cannot achieve. Users can write a series of plugins to extend the existing functions of WAF. This makes it easier to intercept complex vulnerabilities.

## Quick Start

1. Login to the management: Access https://ip:4443 ,the IP address is the server IP address for installing the UUSEC WAF, the default username is `admin`, and the default password is `#Passw0rd`.
2. Add a site: Go to the "Sites" menu, click the "Add Site" button, and follow the prompts to add the site domain name and website server IP.
3. Add SSL certificate: Go to the certificate management menu, click the "Add Certificate" button, and upload the HTTPS certificate and private key file of the domain name. If you don‘t have a SSL certificate, you can also apply for a Let's Encrypt free SSL certificate and renew it automatically before the certificate expires.
4. Change the DNS address of the domain: Go to the domain name service provider's management backend and change the IP address recorded in the DNS A of the domain name to the IP address of the UUSEC WAF server.
5. Test connectivity: Visit the site domain to see if the website can be opened, and check if the returned HTTP header server field is uuWAF.
