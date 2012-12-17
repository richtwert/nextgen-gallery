<?php

/**
 * Contains function(s) to generate a basic pagination widget
 */
class Mixin_Basic_Pagination extends Mixin
{

    /**
     * Returns a formatted HTML string of a pagination widget
     *
     * @param mixed $page
     * @param int $totalElement
     * @param int $maxElement
     * @return array Of data holding prev & next url locations and a formatted HTML string
     */
    public function create_pagination($page, $totalElement, $maxElement = 0)
    {
        $controller = $this->object->get_registry()->get_utility('I_Display_Type_Controller');

        $prev_symbol = apply_filters('ngg_prev_symbol', '&#9668;');
        $next_symbol = apply_filters('ngg_prev_symbol', '&#9658;');

        $return = array('prev' => '', 'next' => '', 'output' => '');

        if ($maxElement <= 0)
            return $return;

        $total = $totalElement;

        // create navigation
        if ($total > $maxElement)
        {
            $r = '';
            if (1 < $page)
            {
                $newpage = (1 == $page - 1) ? 1 : $page - 1;
                $return['prev'] = $controller->add_parameter('page', $newpage);
                $r .=  '<a class="prev" id="ngg-prev-' . $newpage . '" href="' . $return['prev'] . '">' . $prev_symbol . '</a>';
            }

            $total_pages = ceil($total / $maxElement);

            if ($total_pages > 1)
            {
                for ($page_num = 1; $page_num <= $total_pages; $page_num++) {
                    if ($page == $page_num)
                    {
                        $r .=  '<span class="current">' . $page_num . '</span>';
                    }
                    else {
                        if ($page_num < 3 || ($page_num >= $page - 3 && $page_num <= $page + 3) || $page_num > $total_pages - 3)
                        {
                            $newpage = (1 == $page_num ) ? 1 : $page_num;
                            $link = $controller->add_parameter('page', $newpage);
                            $r .= '<a class="page-numbers" href="' . $link . '">' . ($page_num) . '</a>';
                        }
                    }
                }
            }

            if (($page) * $maxElement < $total || -1 == $total)
            {
                $newpage = $page + 1;
                $return['next'] = $controller->add_parameter('page', $newpage);
                $r .=  '<a class="next" id="ngg-next-' . $newpage . '" href="' . $return['next'] . '">' . $next_symbol . '</a>';
            }

            $return['output'] = "<div class='ngg-navigation'>{$r}</div>";
        }
        else {
            $return['output'] = "<div class='ngg-clear'></div>";
        }

        return $return;
    }
}