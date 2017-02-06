<?php

class WepForm
{
    /**
     * @param $label
     * @param $name
     * @param $placeholder
     * @param string $value
     * @param string $params['type']  text,password,email
     * @return string
     */
    public function getTextHtml($label, $name, $value = '', $options = [], $params = [])
    {
        $placeholder = isset($params['placeholder']) ? $params['placeholder'] : '';
        $type = isset($params['type']) ? $params['type'] : 'text';
        $ret = '<div class="am-form-group">
            <label>'.$label.'</label>
            <input type="'.$type.'" class="" name="'.$name.'" value="'.$value.'" placeholder="'.$placeholder.'">
        </div>';

        return $ret;
    }

    public function getFileHtml($label, $name, $value = '', $options = [], $params = [])
    {
        $ret = '<div class="am-form-group am-form-file">
			<label>'.$label.'</label>
			<div>
				<button type="button" class="am-btn am-btn-default am-btn-sm">
					<i class="am-icon-cloud-upload"></i> 选择要上传的文件</button>
			</div>
			<input type="file" name="'.$name.'">
		</div>';

        return $ret;
    }

    public function getCheckboxHtml($label, $name, $value = '', $options = [], $params = [])
    {
        $vals = $value !== '' ? explode(',', $value) : [];
        $ret = '<div class="am-form-group"><label>'.$label.'</label><br>';
        foreach ($options as $key => $val) {
            $checked = in_array($key, $vals) ? ' checked="checked"' : '';
            $ret .= '<label class="am-checkbox-inline">
				<input type="checkbox" name="'.$name.'[]"'.$checked.' value="'.$key.'"> '.$val.'
			</label>';
        }
        $ret .= '</div>';

        return $ret;
    }

    public function getRadioHtml($label, $name, $value = '', $options = [], $params = [])
    {
        $ret = '<div class="am-form-group"><label>'.$label.'</label><br>';
        foreach ($options as $key => $val) {
            $checked = ($key == $value) ? ' checked="checked"' : '';
            $ret .= '<label class="am-radio-inline">
				<input type="radio" name="'.$name.'[]"'.$checked.' value="'.$key.'"> '.$val.'
			</label>';
        }
        $ret .= '</div>';

        return $ret;
    }

    public function getSelectHtml($label, $name, $value = '', $options = [], $params = [])
    {
        $ret = '<div class="am-form-group"><label>'.$label.'</label><select name="'.$name.'">';
        foreach ($options as $key => $val) {
            $checked = ($key == $value) ? ' checked="checked"' : '';
            $ret .= '<option'.$checked.' value="'.$key.'">'.$val.'</option>';
        }
        $ret .= '</select><span class="am-form-caret"></span></div>';

        return $ret;
    }

    public function getMultipleSelectHtml($label, $name, $value = '', $options = [], $params = [])
    {
        $vals = $value !== '' ? explode(',', $value) : [];
        $ret = '<div class="am-form-group"><label>'.$label.'</label><select name="'.$name.'[]" multiple>';
        foreach ($options as $key => $val) {
            $checked = in_array($key, $vals) ? ' checked="checked"' : '';
            $ret .= '<option'.$checked.' value="'.$key.'">'.$val.'</option>';
        }
        $ret .= '</select></div>';

        return $ret;
    }

    public function getTextareaHtml($label, $name, $value = '', $options = [], $params = [])
    {
        $ret = '<div class="am-form-group">
            <label>'.$label.'</label>
            <textarea class="" rows="5" name="'.$name.'">'.$value.'</textarea>
        </div>';

        return $ret;
    }
}