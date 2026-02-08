#include <stdio.h>
#include <string.h>
#include <stdlib.h>

// 模拟的"管理员"功能，正常流程不应被调用
void secret_admin_panel(void) {
    printf("\n========================================\n");
    printf("  [!] 欢迎进入管理员面板！\n");
    printf("  [!] 你成功利用了缓冲区溢出漏洞！\n");
    printf("========================================\n");
    system("/bin/sh");
}

// 存在栈缓冲区溢出漏洞的登录验证函数
int check_password(const char *input) {
    int authenticated = 0;
    char password_buf[64];  // 只分配了 64 字节的缓冲区

    // 漏洞所在：使用 strcpy 而不是 strncpy，
    // 没有对输入长度做任何检查，导致栈缓冲区溢出
    strcpy(password_buf, input);

    if (strcmp(password_buf, "s3cur3_p@ssw0rd") == 0) {
        authenticated = 1;
    }

    return authenticated;
}

int main(int argc, char *argv[]) {
    printf("===================================\n");
    printf("   简易登录系统 v1.0\n");
    printf("===================================\n");

    if (argc < 2) {
        printf("用法: %s <密码>\n", argv[0]);
        return 1;
    }

    printf("[*] 正在验证密码...\n");

    if (check_password(argv[1])) {
        printf("[+] 认证成功！欢迎回来，管理员。\n");
    } else {
        printf("[-] 密码错误，拒绝访问。\n");
    }

    return 0;
}
