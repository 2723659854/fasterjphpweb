<?php

namespace App\Command;

use Root\Lib\BaseCommand;
use Root\Lib\HttpClient;

/**
 * @purpose 用户自定义命令
 * @author administrator
 * @time 2024-05-13 02:22:46
 */
class Demo extends BaseCommand
{

    /** @var string $command 命令触发字段，请替换为你自己的命令，执行：php start.php your:command */
    public $command = 'demo';

    /**
     * 配置参数
     * @return void
     */
    public function configure()
    {
        /** 必选参数 */
        //$this->addArgument('argument','这个是参数argument的描述信息');
        /** 可传参数 */
        //$this->addOption('option','这个是option参数的描述信息');
    }

    /**
     * 请在这里编写你的业务逻辑
     * @return void
     */
    public function handle()
    {
       //$this->sendTcp('54.77.139.23');
       //$this->scanPort('54.77.139.23');
       $this->scanPortAsync('54.77.139.23');
    }



    /**
     * 测试并发请求
     * @param string $host 请求域名
     * @param string $method 请求方法
     * @param int $forkNumber 并发数
     * @param int $requestNumber 每个客户端请求总数
     * @return void
     * @comment 多进程，高频次，高并发，http请求
     */
    public function sendTcp(string $host,string $method = 'GET', int $forkNumber = 100,int $requestNumber = 10000000)
    {
        $this->info("本次高并发请求开始");
        /** 记录所有的子进程 */
        $pids = [];
        for ($i = 0; $i < $forkNumber; $i++) {
            $pid = pcntl_fork();
            if ($pid == -1) {
                die("无法创建子进程");
            } elseif ($pid == 0) {
                $myPid = getmypid();
                // 子进程逻辑
                for ($i = 1; $i <= $requestNumber; $i++) {
                    $response = (HttpClient::request($host, $method, ['lesson_id' => 201],[],[],false));
                    $statusCode = $response->getStatusCode();
                    echo "\r\n-----进程号：{$myPid},第{$i}次请求完成,statusCode:{$statusCode}-----\r\n";
                }
                /** 每一个子进程任务执行完成后，必须exit退出，否则子进程会接着执行for循环，导致创建多个子进程 */
                exit;
            } else {
                /** 记录子进程的pid */
                $pids[] = $pid;
            }
        }
        /** 父进程等待所有子进程结束 */
        foreach ($pids as $pid) {
            pcntl_waitpid($pid, $status);
        }
        $this->info("本次高并发请求结束");
    }

    /**
     * 扫描端口
     * @param string $ip
     * @param int $startPort
     * @param int $endPort
     * @return void
     */
    public function scanPort(string $ip = '127.0.0.1',int $startPort = 0,int $endPort = 65535)
    {
        echo "开始扫描端口\r\n";
        $canUsePorts = [];
        for ($i = $startPort; $i <= $endPort; $i++) {
            $host = $ip.':'.$i;
            $response = (HttpClient::request($host, 'GET',['lesson_id' => 201],[],[],false));
            $statusCode = $response->getStatusCode();
            if ($statusCode == 200) {
                $canUsePorts[] = $host;
            }
            echo "本次请求地址：{$host},响应状态码是：{$statusCode}\r\n";
        }
        echo "端口扫描完成\r\n";
        print_r($canUsePorts);
    }

    /**
     * 异步扫描端口
     * @param string $ip
     * @param int $startPort
     * @param int $endPort
     * @return void
     */
    public function scanPortAsync(string $ip = '127.0.0.1',int $startPort = 0,int $endPort = 65535){
        echo "开始扫描端口\r\n";
        $canUsePorts = [];
        for ($i = $startPort; $i <= $endPort; $i++) {
            $host = $ip.':80';
            echo $host;
            echo "\r\n";
            HttpClient::requestAsync($host, 'GET',['lesson_id' => 201],[],[],function ($response){
                //var_dump($response);
                var_dump('123');
                file_put_contents(app_path().'/response.txt',json_encode($response,JSON_UNESCAPED_UNICODE));
            },function ($error){
                var_dump(555);
            });

        }
        echo "端口扫描完成\r\n";

    }
}