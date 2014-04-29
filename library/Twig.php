<?php
/**
 * Heavilly based on work done in
 * http://willmendesnetoprojects.wordpress.com/2013/01/18
 *     /codeigniter-twig-composer-uma-boa-ideia/
 * and https://gist.github.com/willmendesneto/4541588
 * 
 * @package codeigniter-twig
 * @author  Rogerio Prado de Jesus <rogeriopradoj@gmail.com>
 * @link    https://github.com/rogeriopradoj/codeigniter-twig-composer
 */
class Twig
{
    /**
     * @var CI_Controller
     */
    protected $CI;
 
    /**
     * @var Twig_Environment
     */
    protected $twig;
 
    /**
     * @var string
     */
    protected $template_dir;
 
    /**
     * @var string
     */
    protected $cache_dir;
 
    /**
     * @param bool $debug Set up Twig_Environment instantiation option
     */
    public function __construct($debug = false)
    {
        $this->CI =& get_instance();
        $this->CI->config->load('twig');
 
        log_message('debug', "Twig Autoloader Loaded");
 
        Twig_Autoloader::register();
 
        $this->template_dir = $this->CI->config->item('template_dir');
        $this->cache_dir = $this->CI->config->item('cache_dir');
        $loader = new Twig_Loader_Filesystem($this->template_dir);
 
        $this->twig = new Twig_Environment($loader, array(
            'cache' => $this->cache_dir,
            'debug' => $debug,
        ));
 
        foreach (get_defined_functions() as $functions) {
            foreach ($functions as $function) {
                $this->twig->addFunction($function, new Twig_Function_Function($function));
            }
        }
    }
 
    /**
     * Renders the template with the given data and returns it as string.
     *
     * @param string $template The template name
     * @param array  $data     An array of parameters to pass to the template
     *
     * @return string The rendered template
     */
    public function render($template, $data = array())
    {
        $template = $this->twig->loadTemplate($template);
        return $template->render($data);
    }
 
    /**
     * Displays a template, allows debugging time and memory
     * 
     * @param string  $template The template name
     * @param array   $data     An array of parameters to pass to the template
     * @param boolean $debug    An array of parameters to pass to the template
     */
    public function display($template, $data = array(), $debug = false)
    {
        $template = $this->twig->loadTemplate($template);
        if ($debug) {
            /* elapsed_time and memory_usage */
            $data['elapsed_time'] = $this->CI->benchmark->elapsed_time(
                'total_execution_time_start',
                'total_execution_time_end'
            );
            $memory = (!function_exists('memory_get_usage')) ? '0' : round(memory_get_usage()/1024/1024, 2) . 'MB';
            $data['memory_usage'] = $memory;
        }
 
        $template->display($data);
    }

    /**
     * Registers a Function into Twig.
     *
     * @param string $name The function name
     */
    public function addFunction($name)
    {
        $this->twig->addFunction($name, new Twig_Function_Function($name));
    }
}
