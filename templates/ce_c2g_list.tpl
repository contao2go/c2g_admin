<div id="c2g_list">
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
	<?php if ($this->headline): ?>

		<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
	<?php endif; ?>


	<?php if ($this->infotext): ?>
		<?php echo $this->infotext; ?>
	<?php endif; ?>

	<?php if (is_array($this->items)): ?>
		
		<?php foreach ($this->items as $groupKey => $groupItem): ?>

		
		
				<div class="statechange">
					<a href="<?php echo $this->groupInfo[$groupKey]['stateLink']; ?>">
						<?php echo $this->groupInfo[$groupKey]['stateText']; ?>
					</a>
				</div>
				<div class="toggler_first <?php echo $this->groupInfo[$groupKey]['cssStyle'];?>">
				
				
					<ul>
					<?php foreach ($groupItem as $item): ?>
						<li class="item">
							<div class="textblock">
								<div class="header">
									<?php echo $item['header']; ?>
								</div>
								
								<div class="description">	
									<?php echo $item['description']; ?>	
								</div>
							</div>
							<div class="buttons">
							
								<?php if (is_array($item['buttons'])): ?>
								<ul>
									<?php foreach ($item['buttons'] as $button): ?>
										<li><?php echo $button; ?></li>
									<?php endforeach; ?>
								</ul>
								<?php endif; ?>
							</div>
						</li>
						<li>
						<div class="clear"></div>
						</li>
					<?php endforeach; ?>
					</ul>
						
					
				</div>
				
		<?php endforeach; ?>

		
	<?php endif; ?>
</div>
</div>
